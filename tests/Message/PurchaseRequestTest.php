<?php

namespace Omnipay\Portmone\Message;

use Omnipay\Tests\TestCase;

/**
 * Class PurchaseRequestTest
 * @package Omnipay\Portmone\Message
 */
class PurchaseRequestTest extends TestCase
{
    protected $urlBase;
    protected $dataSets;

    /**
     * @var \Omnipay\Portmone\Message\PurchaseRequest
     */
    protected $request;

    private function setData()
    {
        $this->dataSets = new \stdClass();
        $this->dataSets->purchase = [
            'orderNumber' => '2021-03-21N033',
            'amount' => '12.00',
            'description' => 'Description',
            'successUrl' =>  $this->urlBase . '/success',
            'failureUrl' => $this->urlBase . '/failure',
            'returnUrl' => $this->urlBase . '/return',
            'paymentMethod' => 'portmone,visacheckout,gpay',
//            '' => '',
            'locale' => 'en',
            'preauth' => true,
        ];

        $this->dataSets->purchaseAll = [
            'orderNumber' => '2021-03-21N033',
            'payeeId' => '1185',
            'login' => 'wdishop',
            'password' => 'wdi451',
            'shopSiteId' => 'SHP_333',
            'amount' => '25.00',
            'description' => 'Description Full :: 2',
            'successUrl' => $this->urlBase . '/success',
            'failureUrl' => $this->urlBase . '/failure',
            'returnUrl' => $this->urlBase . '/return',
            'paymentMethod' => 'portmone,privat,kyivstar,visacheckout,gpay',
            'preauth' => false,

            // * optional parameters
            'expTime' => 500,
            'encoding' => 'win-1251',
            'locale' => 'ru',
            'lang' => 'uk',
            'signature' => '',
            'currency' => 'USD',

            'notifyUrl' => $this->urlBase . '/notify',
            'emailAddress' => 'billing@example.com',

        ];
    }

    public function setUp()
    {
        // * set URL
        $this->urlBase = $_ENV['TESTURL'];

        // * init data
        $this->setData();

        // * init request
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());
        $dataset = $this->dataSets->purchase ?: [];
        $this->request->initialize($dataset);
    }

    /**
     * test getting data from request
     */
    public function testGetData()
    {
        $dataset = $this->dataSets->purchaseAll ?: [];
        $this->request->initialize($dataset);
        $data = $this->request->getData();

        $this->assertSame("25.00", $data['order']['billAmount']);
        $this->assertContains('Description', $data['order']['description']);
        $this->assertNotEmpty($data['order']['shopOrderNumber']);
        $this->assertSame("N", $data['order']['preauthFlag']);
        $this->assertContains($this->urlBase.'/', $data['order']['successUrl']);
        $this->assertContains($this->urlBase.'/', $data['order']['failureUrl']);

        $this->assertNotEmpty($data['payee']['payeeId']);
        $this->assertNotEmpty($data['payee']['login']);
        $this->assertNotEmpty($data['payee']['password']);
        $this->assertSame("Y", $data['paymentTypes']['portmone']);

        $this->assertArrayHasKey("card", $data['priorityPaymentTypes']);

        $this->assertSame("uk", $data['payer']['lang']);
        $this->assertCount(9, $data);
    }
}
