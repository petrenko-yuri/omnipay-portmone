<?php
namespace Omnipay\Portmone;

use Omnipay\Common\AbstractGateway;

/**
 *
 */
class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'Portmone';
    }

    public function getDefaultParameters()
    {
        return array();
    }

    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Dummy\Message\CreditCardRequest', $parameters);
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Dummy\Message\CreditCardRequest', $parameters);
    }

    public function completeAuthorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Dummy\Message\TransactionReferenceRequest', $parameters);
    }

    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Dummy\Message\TransactionReferenceRequest', $parameters);
    }

    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Dummy\Message\TransactionReferenceRequest', $parameters);
    }

    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Dummy\Message\TransactionReferenceRequest', $parameters);
    }

    public function void(array $parameters = array())
    {
//        return $this->createRequest('\Omnipay\Dummy\Message\TransactionReferenceRequest', $parameters);
    }

    public function createCard(array $parameters = array())
    {
//        return $this->createRequest('\Omnipay\Dummy\Message\CreditCardRequest', $parameters);
    }

    public function updateCard(array $parameters = array())
    {
//        return $this->createRequest('\Omnipay\Dummy\Message\CardReferenceRequest', $parameters);
    }

    public function deleteCard(array $parameters = array())
    {
//        return $this->createRequest('\Omnipay\Dummy\Message\CardReferenceRequest', $parameters);
    }

    public function fetchTransaction(array $options = [])
    {
    }

    public function acceptNotification(array $options = [])
    {
    }
}
