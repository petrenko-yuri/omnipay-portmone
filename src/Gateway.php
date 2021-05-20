<?php

namespace Omnipay\Portmone;

use Omnipay\Common\AbstractGateway;

//use Omnipay\Portmone\Message\Notification;

/***
 *
 * The main methods implemented by gateways are:

 * `authorize($options)` - authorize an amount on the customer's card
 * `completeAuthorize($options)` - handle return from off-site gateways after authorization
 * `capture($options)` - capture an amount you have previously authorized
 * `purchase($options)` - authorize and immediately capture an amount on the customer's card
 * `completePurchase($options)` - handle return from off-site gateways after purchase
 * `refund($options)` - refund an already processed transaction
 * `void($options)` - generally can only be called up to 24 hours after submitting a transaction
 * `acceptNotification()` - convert an incoming request from an off-site gateway to a generic notification object for
 *  further processing
 */

/**
 * show off @method
 *
// * @method mixed getPayeeId() get PayeeId
// * @method $this setPayeeId(mixed $value) set PayeeId
 */


/**
 * Class Gateway
 * @package Omnipay\Portmone
 * @method \Omnipay\Common\Message\RequestInterface createCard(array $options = array())              (Optional method)
 *         The returned response object includes a cardReference, which can be used for future transactions
 * @method \Omnipay\Common\Message\RequestInterface updateCard(array $options = array())              (Optional method)
 *         Update a stored card
 * @method \Omnipay\Common\Message\RequestInterface deleteCard(array $options = array())              (Optional method)
 *         Delete a stored card
 */
class Gateway extends AbstractGateway
{
    // * order statuses
    const ORDER_PAYED = 'PAYED';
    const ORDER_CREATED = 'CREATED';
    const ORDER_REJECTED = 'REJECTED';
    const ORDER_PREAUTH = 'PREAUTH';

    // * test `PAYEE` data
    const PORTMONE_TEST_PAYED_ID = 1185;
    const PORTMONE_TEST_LOGIN = 'wdishop';
    const PORTMONE_TEST_PASSWORD = 'wdi451';
    const PORTMONE_TEST_EMAIL = 'test@portmone.me';

    use ParameterCommonMethodsTrait;


    /**
     * @var array core parameters
     */
    protected $coreParameters = [
        'lang', 'encoding',
        'login', 'password',
        'payeeId', 'shopOrderNumber', 'shopbillId'
    ];

//    private $testMode =
    public function getName()
    {
        return 'Portmone';
    }

    public function getDefaultParameters()
    {
        return array(
            'payeeId' => '',
            'login' => '',
            'password' => '',
            'signature' => '',

            // * sales channel digital ID
            'shopSiteId' => '',

            // * Order number and alian `shopOrderNumber` alias. . Max 120 characters
            'orderNumber' => '',
            'lang' => 'en', // * ru, en, uk
//            'currency' => 'UAH', // * supported: UAH, USD, EUR, GBP, BYN, KZT, RUB
        );
    }


    /***
     *      Payment methods
     */

    /***
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\Common\Message\RequestInterface
     */
    public function authorize(array $parameters = [])
    {
        $parameters['preauth'] = true;
        return $this->createRequest('\Omnipay\Portmone\Message\PurchaseRequest', $parameters);
    }

    public function completeAuthorize(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Portmone\Message\CompletePurchaseRequest', $parameters);
    }

    /**
     * Fetches information about transactions using one or set of filters:
     * date ranges, order number or status
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function result(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Portmone\Message\ResultRequest', $parameters);
    }

    /**
     * Fetches transaction information by transaction Id
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\Common\Message\RequestInterface
     */
    public function fetchTransaction(array $parameters = [])
    {
        return $this->result($parameters);
    }

    /**
     * Supports Result
     * @return boolean True if this gateway supports the result() method
     */
    public function supportsResult()
    {
        return method_exists($this, 'result');
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\Common\Message\RequestInterface
     */
    public function purchase(array $parameters = [])
    {
        $parameters['preauth'] = false;
        return $this->createRequest('\Omnipay\Portmone\Message\PurchaseRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\Common\Message\RequestInterface
     */
    public function completePurchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Portmone\Message\CompletePurchaseRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\Common\Message\RequestInterface
     */
    public function capture(array $parameters = []) // * confirmPreauth
    {
        return $this->createRequest('\Omnipay\Portmone\Message\CaptureRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\Common\Message\RequestInterface
     */
    public function refund(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Portmone\Message\RefundRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\Common\Message\RequestInterface
     */
    public function void(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Portmone\Message\VoidRequest', $parameters);
    }

    /**
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest|\Omnipay\Common\Message\NotificationInterface
     */
    public function acceptNotification(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Portmone\Message\NotifyRequest', $parameters);
    }
}
