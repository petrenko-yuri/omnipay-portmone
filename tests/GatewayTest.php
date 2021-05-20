<?php

namespace Omnipay\Portmone;

use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    /**
     * @var array gateway options
     */
    protected $gatewayOptions;

    protected $purchaseOptions;
    protected $completePurchaseOptions;
    protected $captureOptions;

    protected $resultOptions;
    protected $fetchOptions;

    protected $refundOptions;
    protected $voidOptions;

    public function setUp()
    {
        parent::setUp();

        // * init gateway options  $_ENV['gateway_'] from environment (see `phpunit.sample.xml`)
        $this->gatewayOptions = [
            'payeeId' => $_ENV['gateway_payeeId'] ?: '',
            'login' => $_ENV['gateway_login'] ?: '',
            'password' => $_ENV['gateway_password'] ?: '',
            'signature' => $_ENV['gateway_signature'] ?: '',

            'lang' => $_ENV['gateway_lang'] ?: 'en',
            'currency' => $_ENV['gateway_currency'] ?: 'UAH',

            // * add unique suffix to `orderNumber`
            'orderNumber' => $_ENV['gateway_orderNumber'] ?: 'Order #' . date('Ymdhis'),
            'shopSiteId' => $_ENV['gateway_shopSiteId'] ?: '',
        ];

        // * init Gateway
        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());

        $this->setMethodOptions();
    }

    /**
     * initialize set of options to test different gateway methods
     */
    protected function setMethodOptions()
    {
        //        $this->validate('orderNumber', 'amount', 'description', 'returnUrl', 'cancelUrl', 'preauth');
        $this->purchaseOptions = $this->addToDefaultOptions([
            'orderNumber' => '2021-04-18N012',
            'description' => 'purchase order description',
//            'preauth' => false,
            'amount' => '15.00',

            'returnUrl' => 'https://test.omnipay.local/gateways/Portmone/completePurchase',
            'cancelUrl' => 'https://test.omnipay.local/gateways/Portmone',
        ]);

        $this->completePurchaseOptions = $this->addToDefaultOptions([
            'id' => 41,
            'transactionId' => '867955508',
            'amount' => '15.00'
        ]);

        $this->captureOptions = $this->addToDefaultOptions([
            'id' => 43,
            'orderNumber' => 'Mock-00000015-2',
            'transactionId' => '867955508',
            'amount' => '15.00'
        ]);

        // * result
        $this->resultOptions = $this->addToDefaultOptions([
            'id' => 50,
//            'transactionId' => '867962131',
            'startDate' => '10.12.2020',
            'endDate' => '31.12.2020',
        ]);

        $this->fetchOptions = $this->addToDefaultOptions([
            'id' => 51,
            'transactionId' => '867962131',
        ]);

        $this->refundOptions = $this->addToDefaultOptions([
            'id' => 52,
            'orderNumber' => 'Mock-00000015-4',
            'transactionId' => '867955508',
            'message' => 'Test return',
            'amount' => '95.00',
        ]);

        $this->voidOptions = $this->addToDefaultOptions([
            'id' => 50,
            'transactionId' => '867955508',
            'amount' => '99.00'
        ]);
    }

    /**
     * merge array with default gateway options
     * @param $arr
     * @return array
     */
    protected function addToDefaultOptions($arr)
    {
        return array_merge($this->gatewayOptions, $arr);
    }


    public function testPurchase()
    {
        $this->setMockHttpResponse('PurchaseRedirect.txt');
        $request = $this->gateway->purchase($this->purchaseOptions);

        $this->assertInstanceOf('Omnipay\Portmone\Message\PurchaseRequest', $request);
        $this->assertSame($this->purchaseOptions['orderNumber'], $request->getOrderNumber());
        $this->assertArrayHasKey('order', $request->getData());

        // * get response
        $response = $request->send();

        // * test response methods
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
    }


    public function testCompletePurchase()
    {
        $this->getHttpRequest()->request->replace(
            array(
                'SHOPBILLID' => '867955508',
                'SHOPORDERNUMBER' => 'Mock-00000010-1',
                'APPROVALCODE' => '366999',
                'BILL_AMOUNT' => '15',
                'TOKEN' => '18383637393535353038096252BDA0962D24B0B88D26D5B13037ECF3474391F560EB8B864A1ADAC614863922AC9DFE9E4887022F281585025544499',
                'RESULT' => '0',
                'CARD_MASK' => '444433******1111',
                'ATTRIBUTE1' => '1',
                'ATTRIBUTE2' => '2',
                'ATTRIBUTE3' => '3',
                'ATTRIBUTE4' => '4',
                'RECEIPT_URL' => 'https:\/\/www.portmone.com.ua\/r3\/services\/receipts\/get-receipts\/shop-bill-id\/3534fadfe625371c3204f111254677de2355a0dd5c3dd7880deff7f4f821dc6a089b855621a5a7bd77accdd531b12ef3dfbef0ae02932c28ef005cfbd7da329d',
                'LANG' => 'en',
                'DESCRIPTION' => 'Опис замовлення (purchase/auth)',
                'IPSTOKEN' => 'null',
                'ERRORIPSCODE' => 'null',
                'ERRORIPSMESSAGE' => 'null',
            )
        );

        $response = $this->gateway->completePurchase($this->completePurchaseOptions)->send();
        $data = $response->getData();

        $this->assertTrue($response->isSuccessful());
        $this->assertNull($response->getMessage());


        // * test data compliance
        $this->assertSame($this->completePurchaseOptions['transactionId'], $data['id']);
        $this->assertSame(doubleval($this->completePurchaseOptions['amount']), doubleval($data['amount']));
    }


    public function testAuthorize()
    {
        $this->setMockHttpResponse('PurchaseRedirect.txt');
        $request = $this->gateway->authorize($this->purchaseOptions);

        $this->assertInstanceOf('Omnipay\Portmone\Message\PurchaseRequest', $request);
        $this->assertSame($this->purchaseOptions['orderNumber'], $request->getOrderNumber());
        $this->assertArrayHasKey('order', $request->getData());

        // * get response
        $response = $request->send();

        // * test response methods
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
    }


    public function testCapture()
    {
        $this->setMockHttpResponse('CaptureSuccess.txt');
        $request = $this->gateway->capture($this->captureOptions);

        $this->assertInstanceOf('Omnipay\Portmone\Message\CaptureRequest', $request);
        $this->assertArrayHasKey('id', $request->getData());

        $response = $request->send();
        $data = $response->getData();

        // * test response methods
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());

        // * test data compliance
        $this->assertSame($request->getTransactionId(), $response->getTransactionId());
        $this->assertSame(doubleval($request->getAmount()), doubleval($response->getAmount()));
        $this->assertEquals('PAYED', $data['status']);
    }


    public function testResult()
    {
        $this->setMockHttpResponse('ResultExist.txt');
        $request = $this->gateway->result($this->resultOptions);

        $this->assertInstanceOf('Omnipay\Portmone\Message\ResultRequest', $request);
        $this->assertArrayHasKey('id', $request->getData());
        $this->assertArrayHasKey('method', $request->getData());

        $response = $request->send();
        $data = $response->getData();
        $data = current($data);

        // * test response methods
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());

        // * test data compliance
        $this->assertSame($request->getTransactionId(), $response->getTransactionId());
        $this->assertEquals(0, $data['errorCode']);
        $this->assertNotEmpty($data['status']);
    }


    public function testFetchTransaction()
    {
        $this->setMockHttpResponse('ResultExist.txt');
        $request = $this->gateway->fetchTransaction($this->fetchOptions);

        $this->assertInstanceOf('Omnipay\Portmone\Message\ResultRequest', $request);
        $this->assertArrayHasKey('id', $request->getData());
        $this->assertArrayHasKey('method', $request->getData());

        $response = $request->send();
        $data = $response->getData();

        // * test response methods
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());

        // * test data compliance
        $this->assertSame($request->getTransactionId(), $response->getTransactionId());
        $this->assertEquals(0, $data['errorCode']);
        $this->assertNotEmpty($data['status']);
    }


    public function testRefund()
    {
        $this->setMockHttpResponse('RefundSuccess.txt');
        $request = $this->gateway->refund($this->refundOptions);

        $this->assertInstanceOf('Omnipay\Portmone\Message\RefundRequest', $request);
        $this->assertArrayHasKey('id', $request->getData());

        $response = $request->send();

        // * test response methods
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());

        // * test data compliance
        $this->assertSame($request->getTransactionId(), $response->getTransactionId());
        $this->assertSame(doubleval($request->getAmount()), -1 * doubleval($response->getAmount()));
    }


    public function testVoid()
    {
        $this->setMockHttpResponse('VoidSuccess.txt');
        $request = $this->gateway->void($this->voidOptions);

        $this->assertInstanceOf('Omnipay\Portmone\Message\VoidRequest', $request);
        $this->assertArrayHasKey('id', $request->getData());

        $response = $request->send();
        $data = $response->getData();

        // * test response methods
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());

        // * test data compliance
        $this->assertSame($request->getTransactionId(), $data['shop_bill_id']);
        $this->assertSame(doubleval($request->getAmount()), doubleval($response->getAmount()));
    }

    public function testNotify()
    {
        $this->getHttpRequest()->request->replace(
            array(
                'data' => file_get_contents(dirname(__DIR__) . '/tests/Mock/notification.xml'),
            )
        );

        $response = $this->gateway->acceptNotification()->send();
        $data = $response->getData();

        $this->assertTrue($response->isSuccessful());

        // * test data compliance
        $this->assertNotNull($data['id']);
        $this->assertNotNull($data['billDate']);
        $this->assertNotNull($data['amount']);
        $this->assertNotNull($data['payeeId']);
    }
}
