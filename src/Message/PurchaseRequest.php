<?php

namespace Omnipay\Portmone\Message;

use Omnipay\Portmone\Gateway;

/**
 * Portmone Purchase Request
 * ToDo[Future]: support token-based payments
 *
 * @method PurchaseResponse send()
 */
class PurchaseRequest extends AbstractRequest
{
    protected $isDebug = false;

    /**
     * initialize default parameters value
     */
    private function initializeDefaults()
    {
        // * init request-specific parameters
        foreach ($this->parameterDefaults as $name => $default_value) {
            if ($this->getParameter($name) === null) {
                $this->setParameter($name, $default_value);
            }
        }
    }

    /**
     * @return array|mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        // * initialize default value for key parameters
        $this->initializeDefaults();

        // * validation
        $this->validate('orderNumber', 'amount', 'description', 'returnUrl', 'cancelUrl', 'preauth');

        // * init data
        $data = array();

        // * order
        $data['order'] = [
            'billAmount' => $this->getAmount(),
            'billCurrency' => $this->getCurrency(),

            'shopOrderNumber' => $this->getOrderNumber(),
            'description' => $this->getDescription(),
            'preauthFlag' => $this->getPreauth() ? 'Y' : 'N',

            'successUrl' => $this->getReturnUrl(),
            'failureUrl' => $this->getCancelUrl(),
        ];

        // * payee (receiver)
        $data['payee'] = [
            'payeeId' => $this->getPayeeId(),
            'login' => $this->getLogin(),
            'password' => $this->getPassword(),
            'signature' => $this->getSignature(),
            'shopSiteId' => $this->getShopSiteId(),
        ];

        // * payer (sender)
        $data['payer'] = [
            'lang' => ($locale = $this->getLang()) ? $locale : 'uk',
            'emailAddress' => $this->getEmailAddress(),
        ];

        $data['payer']['showEmail'] = (!empty($data['payer']['emailAddress'])) ? $this->getShowEmail() : 'N';


        // * handle Payment Types
        $data['paymentTypes'] = $this->getPaymentTypes();

        // * priorityPaymentTypes
        $data['priorityPaymentTypes'] = $this->getPaymentTypePriority();

        // * auto-payment
        $data['autopayment'] = $this->getAutopayment();

        // * token
        $data['token'] = $this->getTokenDataset();

        // * shipping
        $data['shipping'] = $this->getShipping();

        // * style
        $data['style'] = $this->getStyleDataset();

        return $data;
    }

    /**
     * @return array
     */
    private function getShipping()
    {
        // * ToDo: implement shipping support
        return [
            'services' => [],
            'enable' => 'N',
            'required' => 'N',
        ];
    }

    /**
     * get `auto-payment` data subset
     * @return array|null
     */
    private function getAutopayment()
    {
        return null;

        // * ToDo: implement autopayment setup
//        return [
//            'show' => 'N',
//            'edit' => 'N',
//        ];
    }

    /**
     * get `style` data subset
     * @return array|null
     */
    private function getStyleDataset()
    {
        return null;

        // * ToDo: implement `style` support
//        return [
//            'type' => 'brand',
//            'logo' => '', // * url
//            'logoWidth' => '100px',
//            'logoHeight' => '100px',
//            'backgroundColorHeader' => '#ff0000',
//            'backgroundColorButtons' => '#4bbe3f',
//            'colorTextAndIcons' => '#4bbe3f',
//            'borderColorList' => '#3e77aa',
//            'bcMain' => '#4bbe3f',
//        ];
    }

    /**
     * get `token` data subset
     * @return array|null
     */
    private function getTokenDataset()
    {
        return null;

        // * ToDo: implement token support
//        return [
//            'tokenFlag' => 'N',
//            'returnToken' => 'N',
//            'token' => '',
//            'cardMask' => '',
//            'otherPaymentMethods' => 'N',
//        ];
    }

    /**
     * get payment types priority subset
     * @return array
     */
    private function getPaymentTypePriority()
    {
        // * ToDo: get priority order from `paymentMethod` string
        return [
            'card' => '1',
            'gpay' => '2',
            'applepay' => '3',
            'privat' => '4', // * cannot use `preauth` mechanism
            'visacheckout' => '5',
            'masterpass' => '6',
            'kyivstar' => '7',
            'portmone' => '8',
            'qr' => '9',
            'token' => '10',
            'installment' => '11',
        ];
    }

    /**
     * get payment types availability subset
     *
     * @return array
     */
    private function getPaymentTypes()
    {
        // * does payment has at least one `only`-type excluding all other
        $hasPaymentTypeOnly = (
            $this->hasPaymentType('gpayonly') ||
            $this->hasPaymentType('applepayonly') ||
            $this->hasPaymentType('createtokenonly')
        );

        // * scenarios: `only` or multiply-typed case
        if ($hasPaymentTypeOnly) {
            $types = [
                'card' => 'N',
                'portmone' => 'N',
                'token' => 'N',
                'masterpass' => 'N',
                'visacheckout' => 'N',
                'gpay' => 'N',
                'applepay' => 'N',
                'kyivstar' => 'N',
                'installment' => 'N',
                'qr' => 'N',
                'privat' => 'N', // * cannot use `preauth` mechanism
                'createtokenonly' => $this->hasPaymentType('createtokenonly') ? 'Y' : 'N',
                'gpayonly' => (
                    $this->hasPaymentType('gpayonly') &&
                    !$this->hasPaymentType('createtokenonly')
                ) ? 'Y' : 'N',
                'applepayonly' => (
                    $this->hasPaymentType('applepayonly') &&
                    !$this->hasPaymentType('gpayonly') &&
                    !$this->hasPaymentType('createtokenonly')
                ) ? 'Y' : 'N',
            ];
        } else {
            $types = [
                'card' => !$this->hasPaymentType('token') ? 'Y' : 'N',
                'portmone' => $this->hasPaymentType('portmone') ? 'Y' : 'N',
                'token' => $this->hasPaymentType('token') ? 'Y' : 'N',
                'masterpass' => $this->hasPaymentType('masterpass') ? 'Y' : 'N',
                'visacheckout' => $this->hasPaymentType('visacheckout') ? 'Y' : 'N',
                'gpay' => $this->hasPaymentType('gpay') ? 'Y' : 'N',
                'applepay' => $this->hasPaymentType('applepay') ? 'Y' : 'N',
                'kyivstar' => $this->hasPaymentType('kyivstar') ? 'Y' : 'N',
                'installment' => $this->hasPaymentType('installment') ? 'Y' : 'N',
                'qr' => $this->hasPaymentType('qr') ? 'Y' : 'N',
                'privat' => $this->hasPaymentType('privat') ? 'Y' : 'N',
                'gpayonly' => 'N',
                'applepayonly' => 'N',
                'createtokenonly' => 'N',
            ];
        }


        return $types;
    }

    /**
     * indicates if the payment type is present in the `payment types` string
     * @param $type
     * @return bool
     */
    private function hasPaymentType($type)
    {
        $method = "," . str_replace(' ', '', $this->getPaymentMethod()) . ",";
        return (strpos(strtolower($method), strtolower(',' . $type . ',')) !== false);
    }

    /**
     * initialize data values for testing
     * @param $data
     * @return mixed
     */
    private function updateWithTestData($data)
    {
        $data['order']['shopOrderNumber'] = $data['order']['shopOrderNumber'] ?: 'demo_order_12345';
        $data['payee']['payeeId'] = $data['payee']['payeeId'] ?: Gateway::PORTMONE_TEST_PAYED_ID; //  1185
        $data['payee']['login'] = $data['payee']['login'] ?: Gateway::PORTMONE_TEST_LOGIN;
        $data['payee']['password'] = $data['payee']['password'] ?: Gateway::PORTMONE_TEST_PASSWORD;
        $data['payer']['emailAddress'] = $data['payer']['emailAddress'] ?: Gateway::PORTMONE_TEST_EMAIL;

        return $data;
    }

    /**
     * @param mixed $data
     * @return \Omnipay\Common\Message\ResponseInterface|PurchaseResponse
     */
    public function sendData($data)
    {
        // * tmp
        $data = $this->updateWithTestData($data);

        $response = $this->sendRequest('POST', '/', $data);
        return $this->response = new PurchaseResponse($this, $response);
    }
}
