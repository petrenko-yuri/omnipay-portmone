<?php

namespace Omnipay\Portmone\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Class PurchaseResponse
 * @package Omnipay\Portmone\Message
 */
class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * When you do a `purchase` the request is never successful because
     * you need to redirect off-site to complete the purchase.
     *
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return false;
    }
}
