<?php

namespace Omnipay\Portmone\Message;

/**
 * Class RefundRequest
 * @package Omnipay\Portmone\Message
 */
class RefundRequest extends AbstractRequest
{
    protected $isDebug = false;

    /**
     * @return array|mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('payeeId', 'orderNumber', 'amount', 'message'); // 'id', 'transactionId'

        $data = [
            'method' => "return",
            'id' => $this->getId(),
        ];

        $data['params']['data'] = [
            'payeeId' => $this->getPayeeId(),
            'login' => $this->getLogin(),
            'password' => $this->getPassword(),

            'shopOrderNumber' => $this->getOrderNumber(),
            'shopbillId' => $this->getTransactionId(),
            'message' => $this->getMessage(),

            'returnAmount' => ($this->getAmountInteger() > 0) ? $this->getAmount() : null,
        ];

        $this->showDebug($data, 'Refund (getData)');
        return $data;
    }

    /**
     * @param mixed $data
     * @return \Omnipay\Common\Message\ResponseInterface|Response
     */
    public function sendData($data)
    {
        $this->showDebug($data, 'Refund (data)');
        $response = $this->sendRawJsonRequest('POST', '/', $data);
        $this->showDebug($response, 'Refund (response)');

        return $this->response = new Response($this, $response);
    }
}
