<?php

namespace Omnipay\Portmone\Message;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Class CompletePurchaseRequest
 * @package Omnipay\Portmone\Message
 */
class CompletePurchaseRequest extends AbstractRequest
{
    protected $isDebug = false;

    protected $parametersToPass = [
        'SHOPORDERNUMBER' => 'orderNumber',
        'BILL_AMOUNT' => 'amount',
        'APPROVALCODE' => 'approvalCode',
        'RECEIPT_URL' => 'receiptUrl',
        'TOKEN' => 'token',
        'TOKEN_EXP_DATE' => 'tokenExpireDate',
        'CARD_MASK' => 'cardMask',
        'IPSTOKEN' => 'ipsToken',
        'ERRORIPSCODE' => 'errorIpsCode',
        'ERRORIPSMESSAGE' => 'errorIpsMessage',
        'BARCODE' => 'barcode',
        'SHIPMENTUUID' => 'shipmentUuid',
        'COSTOFDELIVERY' => 'costOfDelivery',

        // * authorize-specific
        'RRN' => 'rrn',
        'MERCHANT_ID' => 'merchantId',
        'TERMINAL_ID' => 'terminalId',
        'BANK_ID' => 'bankId',
    ];

    /**
     * @return array|mixed|null
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->showDebug($this->httpRequest->request->all(), 'request->all');
        $this->showDebug($this->getParameters(), 'CompletePurchase params');

        $amount = $this->httpRequest->request->get('BILL_AMOUNT') ?: null;
        if ($amount) {
            $this->setAmount($amount);
        }

        $this->validate('amount');

        $data = [];

        // * process `TransactionReference`
        $data['id'] = $this->httpRequest->request->get('SHOPBILLID') ?: null;
        if (!empty($data['id'])) {
            $this->setTransactionId($data['id']);
        } else {
            $data['id'] = $this->getTransactionId();
        }

        if (empty($data['id'])) {
            throw new InvalidRequestException("The transactionReference parameter is required");
        } else {
            $data['reference'] = $data['id'];
        }

        // * pass parameters
        $data = $this->addRequestParametersToData($data);

        // * success
        $result = $this->httpRequest->request->get('RESULT');
        $data['success'] = ($result === '0') ? true : false;

        return $data;
    }

    /**
     * @param mixed $data
     * @return \Omnipay\Common\Message\ResponseInterface|Response
     */
    public function sendData($data)
    {
        $this->showDebug($data, 'Send data');

        return $this->response = new Response($this, $data);
    }
}
