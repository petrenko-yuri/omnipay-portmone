<?php
/**
 * Created by PhpStorm.
 * User: Yuri Petrenko
 * Date: 03.05.21
 * Time: 22:37
 */

namespace Omnipay\Portmone\Message;

use Omnipay\Common\Http\Exception;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\NotificationInterface;
use Omnipay\Common\Http\ClientInterface;

/**
 * Server Notification.
 * The gateway will send the results of Server transactions here.
 */

/**
 * Class NotifyRequest
 * @package Omnipay\Portmone\Message
 */
class NotifyRequest extends AbstractRequest implements NotificationInterface
{
    /**
     * Valid status responses, to return to the gateway.
     */
    const RESPONSE_STATUS_OK = 'OK';
    const RESPONSE_STATUS_ERROR = 'ERROR';
    const RESPONSE_STATUS_INVALID = 'INVALID';
    const RESPONSE_STATUS_BAD_REQUEST = 'BAD_REQUEST';

    protected $dataMap = [
        'PAYEE.NAME' => 'payeeName',
        'PAYEE.CODE' => 'payeeId',

        'BANK.NAME' => 'bankName',
        'BANK.CODE' => 'bankCode',
        'BANK.ACCOUNT' => 'bankAccount',


        'BILL_ID' => 'id',
        'BILL_NUMBER' => 'orderNumber',
        'BILL_DATE' => 'billDate',
        'BILL_PERIOD' => 'billPeriod',

        'PAY_DATE' => 'paymentDate',
        'PAYED_AMOUNT' => 'amount',
        'PAYED_COMMISSION' => 'commission',
        'PAYED_DEBT' => 'debt', // * 0 by default

        'AUTH_CODE' => 'approvalCode',

        'PAYER.CONTRACT_NUMBER' => 'clientIdType',
        'PAYER.ATTRIBUTE1' => 'clientIdValue',
    ];

    /**
     * Line separator for return message to the gateway.
     */
    const LINE_SEP = "\r\n";

    /**
     * Copy of the POST data sent in.
     */
    protected $data;

    /**
     * NotifyRequest constructor.
     * Initialise the data from the server request.
     * @param ClientInterface $httpClient
     * @param HttpRequest $httpRequest
     */
    public function __construct(ClientInterface $httpClient, HttpRequest $httpRequest)
    {
        parent::__construct($httpClient, $httpRequest);
        $this->data = $httpRequest->request->all();
    }

    /**
     * indicates is received data valid or not
     * @return bool
     */
    protected function isValid()
    {
        return isset($this->data['payeeId']) && isset($this->data['id']) &&
            isset($this->data['amount']) && isset($this->data['paymentDate']);
    }

    /**
     * transforms XML-string data to array
     * @param $content
     * @return mixed
     */
    protected function xml2json($content)
    {
        // convert xml string into an object
        $document = simplexml_load_string($content);

        // process object into json
        $str = json_encode($document);

        // convert `json` into associative array
        $result = json_decode($str, true);

        return $result;
    }

    /**
     * unserialize XML data from payment system and populate data to notification `data`
     * @return array|mixed
     * @throws InvalidResponseException
     */
    public function getData()
    {
        $data = $this->data['data'] ?: null;
        if ($data) {
            try {
                // Convert xml string into an array
                $data = $this->xml2json($data);
            } catch (Exception $exception) {
                $this->setExitOnResponse(true);
                $this->badRequest('', "Incorrect or incomplete XML");
            }

            $this->populateData($data);
        }

        if (!$this->isValid()) {
            throw new InvalidResponseException('Data is invalid. Cannot confirm notification', 422);
        } else {
            $this->data['errorCode'] = 0;
            unset($this->data['data']);
        }

        return $this->data;
    }

    /**
     * rename data keys and put it into local object's data
     * @param $data
     * @return bool
     */
    protected function populateData($data)
    {
        if ($data && isset($data['BILL'])) {
            $data = $data['BILL'];
            foreach ($this->dataMap as $path => $name) {
                $this->data[$name] = $this->getDotValue($data, $path);
            }

            return true;
        }

        return false;
    }

    /**
     *
     * @param mixed $data ignored
     * @return \Omnipay\Common\Message\ResponseInterface|Response
     */
    public function sendData($data)
    {
        return $this->response = new Response($this, $data);
    }

    /**
     * Confirm
     *
     * Notify payment system you received the payment details and wish to confirm the payment.
     *
     * @param string $nextUrl URL to forward the customer to.
     * @param string|null $detail Optional human readable reasons for accepting the transaction.
     * @throws InvalidResponseException
     */
    public function confirm($nextUrl, $detail = null)
    {
        // If the signature is invalid, then do not allow the confirm.
        if (!$this->isValid()) {
            throw new InvalidResponseException('Cannot confirm an invalid notification');
        }

        $this->sendResponse(static::RESPONSE_STATUS_OK, $nextUrl, $detail);
    }

    /**
     * Alias for confirm(), trying to define some more general conventions.
     */
    public function accept($nextUrl, $detail = null)
    {
        return $this->confirm($nextUrl, $detail);
    }


    /**
     * Error
     *
     * Notify payment system you received the payment details but there was an error and the payment
     * cannot be completed.
     *
     * @param string URL to foward the customer to.
     * @param string Optional human readable reasons for not accepting the transaction.
     * @throws InvalidResponseException
     */
    public function error($nextUrl, $detail = null)
    {
        // If the signature is invalid, then do not allow the reject.

        if (!$this->isValid()) {
            throw new InvalidResponseException('Cannot reject an invalid notification');
        }

        $this->sendResponse(static::RESPONSE_STATUS_ERROR, $nextUrl, $detail);
    }

    /**
     * Alias for error(), trying to define some more general conventions.
     */
    public function reject($nextUrl, $detail = null)
    {
        return $this->error($nextUrl, $detail);
    }

    /**
     * Invalid
     *
     * Notify payment system you received *something* but the details were invalid and no payment
     * cannot be completed. Invalid should be called if you are not happy with the contents
     * of the POST.
     *
     * @param string URL to foward the customer to.
     * @param string Optional human readable reasons for not accepting the transaction.
     */
    public function invalid($nextUrl, $detail = null)
    {
        $this->sendResponse(static::RESPONSE_STATUS_INVALID, $nextUrl, $detail);
    }

    /**
     * @param $nextUrl
     * @param null $detail
     */
    public function badRequest($nextUrl, $detail = null)
    {
        $this->sendResponse(static::RESPONSE_STATUS_BAD_REQUEST, $nextUrl, $detail);
    }

    /**
     * Construct the response body.
     *
     * @param string The status to send to gateway, one of static::RESPONSE_STATUS_*
     * @param string URL to forward the customer to.
     * @param string Optional human readable reason for this response.
     * @return string
     */
    public function getResponseBody($status, $nextUrl, $detail = null)
    {
        $body = [
            'Status=' . $status,
//            'RedirectUrl=' . $nextUrl,
        ];

        if ($detail !== null) {
            $body[] = 'StatusDetail=' . $detail;
        }

        return implode(static::LINE_SEP, $body);
    }

    /**
     * Respond to gateway confirming or rejecting the notification.
     *
     * @param string The status to send to gateway, one of static::RESPONSE_STATUS_*
     * @param string URL to forward the customer to.
     * @param string Optional human readable reason for this response.
     */
    public function sendResponse($status, $nextUrl, $detail = null)
    {
        // * set HTTP code
        http_response_code($this->getResponseCodeByStatus($status));

        // * output message
        $message = $this->getResponseBody($status, $nextUrl, $detail);

        echo $message;

        if ((bool)$this->getExitOnResponse()) {
            exit;
        }
    }

    protected function getResponseCodeByStatus($status)
    {
        switch ($status) {
            case static::RESPONSE_STATUS_BAD_REQUEST:
                $code = 400;
                break;
            case static::RESPONSE_STATUS_INVALID:
                $code = 422;
                break;
            case static::RESPONSE_STATUS_ERROR:
                $code = 404;
                break;
            case static::RESPONSE_STATUS_OK:
            default:
                $code = 200;
        }

        return $code;
    }


    /**
     * @return mixed true if the notify reponse exits the application.
     */
    public function getExitOnResponse()
    {
        return $this->getParameter('exitOnResponse');
    }

    public function setExitOnResponse($value)
    {
        return $this->setParameter('exitOnResponse', !empty($value));
    }

    /**
     * Overrides the Form/Server/Direct method since there is no
     * getRequest() to inspect in a notification.
     */
    public function getTransactionId()
    {
        if ($data = $this->getData()) {
            return $data->id;
        }

        return null;
    }



    /**
     * Was the transaction successful?
     *
     * @return string Transaction status, one of {@see STATUS_COMPLETED}, {@see #STATUS_PENDING},
     * or {@see #STATUS_FAILED}.
     * @throws InvalidResponseException
     */
    public function getTransactionStatus()
    {
        if ($data = $this->getData()) {
            return NotificationInterface::STATUS_COMPLETED;
//            return NotificationInterface::STATUS_PENDING;
        }

        return NotificationInterface::STATUS_FAILED;
    }
}
