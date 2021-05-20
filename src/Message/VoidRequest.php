<?php

namespace Omnipay\Portmone\Message;

/**
 * Class VoidRequest
 * @package Omnipay\Portmone\Message
 */
class VoidRequest extends AbstractRequest
{
    protected $isDebug = false;

    /**
     * @return array|mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('payeeId', 'id', 'transactionId');

        $data = [
            'method' => "rejectPreauth",
            'id' => $this->getId(),
        ];

        $data['params']['data'] = [
            'payeeId' => $this->getPayeeId(),
            'login' => $this->getLogin(),
            'password' => $this->getPassword(),

            'shopOrderNumber' => $this->getOrderNumber(),
            'shopbillId' => $this->getTransactionId(),
        ];

        return $data;
    }

    /**
     * @param mixed $data
     * @return \Omnipay\Common\Message\ResponseInterface|Response
     */
    public function sendData($data)
    {
        $this->showDebug($data, 'Void (data)');
        $response = $this->sendRawJsonRequest('POST', '/', $data);

        $this->showDebug($response, 'Void (response)');

        return $this->response = new Response($this, $response);
    }
}
