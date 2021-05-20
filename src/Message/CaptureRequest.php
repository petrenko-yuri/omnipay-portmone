<?php

namespace Omnipay\Portmone\Message;

/**
 * Class CaptureRequest
 * @package Omnipay\Portmone\Message
 */
class CaptureRequest extends AbstractRequest
{
    protected $isDebug = false;

    /**
     * @return array|mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('payeeId', 'id', 'orderNumber', 'amount'); // 'transactionId',

        $data = [
            'method' => "confirmPreauth",
            'id' => $this->getId(),
        ];

        $data['params']['data'] = [
            'payeeId' => $this->getPayeeId(),
            'login' => $this->getLogin(),
            'password' => $this->getPassword(),

            'shopOrderNumber' => $this->getOrderNumber(),
            'shopbillId' => $this->getTransactionId(),
            'token' => $this->getToken(),

            'postauthAmount' => ($this->getAmountInteger() > 0) ? $this->getAmount() : null,
        ];

        $this->showDebug($data, 'Data (requested)');

        return $data;
    }

    /**
     * @param mixed $data
     * @return \Omnipay\Common\Message\ResponseInterface|Response
     */
    public function sendData($data)
    {
        $this->showDebug($data, 'Result (data)');
        $response = $this->sendRawJsonRequest('POST', '/', $data);

        $this->showDebug($response, 'Result (response)');

        // * fix of improper behaviour of returned data
        // * Review[@PortmoneTeam]: on the 1st call API returns result as array of items, on the next calls item itself
        if (isset($response[0])) {
            $response = current($response);
        }

        return $this->response = new Response($this, $response);
    }
}
