<?php

namespace Omnipay\Portmone\Message;

use Omnipay\Common\Message\RequestInterface;

/**
 * Class Response
 * @package Omnipay\Portmone\Message
 */
class Response extends AbstractResponse
{
    /**
     * Response constructor.
     * @param RequestInterface $request
     * @param $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

        $this->data = $this->unifyNames();
        $this->initTransactionId();
        $this->initAmount();
    }

    /**
     * @param $data
     * @return bool
     */
    protected function hasNoError($data)
    {
        // * handle error code
        // * Review[@PortmoneTeam]: different names for `errorCode` in different modes: `result`, `capture`, etc
        if (isset($data['errorCode'])) {
            return $data['errorCode'] == '0';
        } elseif (isset($data['error_code'])) {
            return $data['error_code'] == '0';
        }

        // * Review[@PortmoneTeam]: code should not be verified on different nest level. It location should be unified
        if (isset($data[0])) {
            return $this->hasNoError($data[0]);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        if ($this->hasNoError($this->data)) {
            return true;
        }

        return
            isset($this->data['status']) && $this->data['status'] == 'PAYED' ||
            isset($this->data['success']) && $this->data['success'];
    }

    /**
     * unify data property names: - camel case(1st way) or underscored(2nd way)
     * @param null $data
     * @return array|null
     */
    private function unifyNames($data = null)
    {
        $data = $data ?: $this->data;
        if (empty($data)) {
            return null;
        }

        foreach ($data as $name => $value) {
            if (is_int($name)) {
                $data[$name] = $this->unifyNames($value);
            } elseif (is_string($name) && $name != '') {
                $data[$this->makeCamelCase($name)] = $value;
            }
        }

        return $data;
    }

    /**
     * converts `_` delimited string to camel case string
     * @param $value
     * @return string
     */
    protected function makeCamelCase($value)
    {
        if (strpos($value, '_') !== false) {
            $parts = explode('_', $value);
            for ($i = 0; $i < count($parts); $i++) {
                $v = strtolower($parts[$i]);
                // * uppercase first letter
                $parts[$i] = ($i > 0) ? ucfirst($v) : $v;
            }

            $value = join('', $parts);
        }

        return $value;
    }

    /**
     * init transaction `id` data property
     */
    protected function initTransactionId()
    {
        if (!isset($this->data['id'])) {
            foreach (['shopBillId', 'shop_bill_id', 'shopbillId'] as $param) {
                if (isset($this->data[$param]) && !empty($this->data[$param])) {
                    $this->data['id'] = $this->data[$param];
                    break;
                }
            }
        }
    }

    /**
     * init `amount` data property
     */
    protected function initAmount()
    {
        $keys = [
            'bill_amount', 'billAmount', 'postauth_amount', 'postauthAmount',
            'PAYED_AMOUNT', 'return_amount', 'returnAmount'
        ];

        if (!isset($this->data['amount'])) {
            foreach ($keys as $param) {
                if (isset($this->data[$param]) && !empty($this->data[$param])) {
                    $this->data['amount'] = $this->data[$param];
                    break;
                }
            }
        }
    }
}
