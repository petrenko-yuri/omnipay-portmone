<?php
/**
 * Created by PhpStorm.
 * User: Yuri Petrenko
 * Date: 07.04.21
 * Time: 19:54
 */

namespace Omnipay\Portmone;

trait ParameterCommonMethodsTrait
{
    /**
     * @return string
     */
    public function getPayeeId()
    {
        return $this->getParameter('payeeId');
    }

    public function setPayeeId($value)
    {
        return $this->setParameter('payeeId', $value);
    }


    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->getParameter('orderNumber');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setOrderNumber($value)
    {
        return $this->setParameter('orderNumber', $value);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getParameter('description');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setDescription($value)
    {
        return $this->setParameter('description', $value);
    }

    /**
     * @return string
     */
    public function getPreauth()
    {
        return $this->getParameter('preauth');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setPreauth($value)
    {
        return $this->setParameter('preauth', !empty($value));
    }

    /**
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->getReturnUrl();
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setSuccessUrl($value)
    {
        return $this->setReturnUrl($value);
    }

    /**
     * @return string
     */
    public function getFailureUrl()
    {
        return $this->getCancelUrl();
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setFailureUrl($value)
    {
        return $this->setCancelUrl($value);
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->getParameter('lang');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setLang($value)
    {
        return $this->setParameter('lang', $value);
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->getParameter('locale');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setLocale($value)
    {
        return $this->setParameter('locale', $value);
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->getParameter('encoding');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setEncoding($value)
    {
        return $this->setParameter('encoding', $value);
    }


    /**
     * @return string
     */
    public function getExpTime()
    {
        return $this->getParameter('expTime');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setExpTime($value)
    {
        return $this->setParameter('expTime', $value);
    }


    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->getParameter('login');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setLogin($value)
    {
        return $this->setParameter('login', $value);
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->getParameter('signature');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setSignature($value)
    {
        return $this->setParameter('signature', $value);
    }

    /**
     * @return string
     */
    public function getShopOrderNumber()
    {
        return $this->getParameter('shopOrderNumber');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setShopOrderNumber($value)
    {
        return $this->setParameter('shopOrderNumber', $value);
    }

    /**
     * @return string
     */
    public function getShopbillId()
    {
        return $this->getParameter('shopbillId');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setShopbillId($value)
    {
        return $this->setParameter('shopbillId', $value);
    }


    /**
     * @return string
     */
    public function getShopSiteId()
    {
        return $this->getParameter('shopSiteId');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setShopSiteId($value)
    {
        return $this->setParameter('shopSiteId', $value);
    }


    /**
     * @return string
     */
    public function getReturnAmount()
    {
        return $this->getParameter('returnAmount');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setReturnAmount($value)
    {
        return $this->setParameter('returnAmount', $value);
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->getParameter('message');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setMessage($value)
    {
        return $this->setParameter('message', $value);
    }

    /**
     * @return string
     */
    public function getPostauthAmount()
    {
        return $this->getParameter('postauthAmount');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setPostauthAmount($value)
    {
        return $this->setParameter('postauthAmount', $value);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getParameter('status');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setStatus($value)
    {
        return $this->setParameter('status', $value);
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->getParameter('startDate');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setStartDate($value)
    {
        return $this->setParameter('startDate', $value);
    }

    /**
     * @return string
     */
    public function getEndDate()
    {
        return $this->getParameter('endDate');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setEndDate($value)
    {
        return $this->setParameter('endDate', $value);
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->getParameter('emailAddress');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setEmailAddress($value)
    {
        return $this->setParameter('emailAddress', $value);
    }

    /**
     * @return string
     */
    public function getShowEmail()
    {
        return $this->getParameter('showEmail')?:"N";
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setShowEmail($value)
    {
        return $this->setParameter('showEmail', empty($value) ? "N" : "Y");
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getParameter('id');
    }

    /**
     * @param  string $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->setParameter('id', $value);
    }


    /**
     * @param $data
     * @param $path
     * @return mixed
     */
    public function getDotValue($data, $path)
    {
//        $src = $data;
        foreach (explode('.', $path) as $part) {
            if (isset($data[$part])) {
                $data = $data[$part];
            } else {
                return null;
            }
        }

        return $data;
    }
}
