<?php

namespace Omnipay\Portmone\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Class AbstractResponse
 * @package Omnipay\Portmone\Message
 */
class AbstractResponse extends \Omnipay\Common\Message\AbstractResponse implements RedirectResponseInterface
{
    /**
     * {@inheritdoc}
     */
    public function isRedirect()
    {
        return isset($this->data['links']['paymentUrl']);
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUrl()
    {
        if ($this->isRedirect()) {
            return $this->data['links']['paymentUrl'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return !$this->isRedirect() && !isset($this->data['error']);
    }

    /**
     * returns error message
     * @return string|mixed|null
     */
    public function getErrorMessage()
    {
        if (isset($this->data['errorMessage'])) {
            return $this->data['errorMessage'];
        } elseif (isset($this->data['error_message'])) {
            return $this->data['error_message'];
        }

        return null;
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        return isset($this->data['message']) ? $this->data['message'] : null;
    }


    /**
     * @return mixed
     */
    public function getTransactionReference()
    {
        return isset($this->data['id']) ? $this->data['id'] : null; // * reference
    }


    public function getTransactionId()
    {
        if (isset($this->data['id'])) {
            return $this->data['id'];
        }

        return isset($this->data['SHOPBILLID']) ? $this->data['SHOPBILLID'] : null;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        if (isset($this->data['status'])) {
            return $this->data['status'];
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        if (isset($this->data['amount'])) {
            return $this->data['amount'];
        }

        return null;
    }
}
