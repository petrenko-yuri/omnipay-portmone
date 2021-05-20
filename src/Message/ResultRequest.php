<?php

namespace Omnipay\Portmone\Message;

/**
 * Class ResultRequest
 * @package Omnipay\Portmone\Message
 */
class ResultRequest extends AbstractRequest
{
    protected $isDebug = false;

    /**
     * @return array|mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('payeeId', 'id'); // , 'transactionId'

        $data = [
            'method' => "result",
            'id' => $this->getId(),
        ];

        $data['params']['data'] = [
            'payeeId' => $this->getPayeeId(),
            'login' => $this->getLogin(),
            'password' => $this->getPassword(),

            'shopOrderNumber' => $this->getOrderNumber(),
            'shopbillId' => $this->getTransactionId(),
            'status' => $this->getStatus()?:'', // * PAYED, CREATED, REJECTED. '' - any

            'startDate' => $this->getStartDate()?:'', // * date('d.m.Y', time()-3600*24*30)
            'endDate' => $this->getEndDate()?:'', // * date('d.m.Y', time())
        ];

//        var_dump($data);

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

        // * return a single entry when searching by `id` only
        if (isset($response[0]) && $this->getDotValue($data, 'params.data.shopbillId')) {
            $response = current($response);
        }

        return $this->response = new Response($this, $response);
    }
}
