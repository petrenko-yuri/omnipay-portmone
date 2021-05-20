<?php

namespace Omnipay\Portmone\Message;

use Omnipay\Portmone\ParameterCommonMethodsTrait;

/**
 * Class AbstractRequest
 * @package Omnipay\Portmone\Message
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $endpoint = 'https://www.portmone.com.ua/gateway';
    protected $isDebug = false;


    /**
     * @var array core parameters
     */
    protected $coreParameters = [
        'lang', 'encoding',
        'login', 'password',
        'payeeId', 'shopOrderNumber', 'shopbillId'
    ];

    protected $parameterDefaults = [
        'expTime' => 1000,
        'encoding' => 'UTF-8',
        'lang' => '',
        'signature' => '',
    ];


    /**
     * @var array parameters to pass into request data
     */
    protected $parametersToPass = [];


    use ParameterCommonMethodsTrait;

    /**
     * @param $value
     * @param string $title
     */
    protected function showDebug($value, $title = '')
    {
        if ($this->isDebug) {
            if (is_string($value)) {
                $value = (!empty($title) ? "{$title}: " : '') . $value;
            } else {
                if (!empty($title)) {
                    echo "{$title}: <br>" . PHP_EOL;
                }
            }

            var_dump($value);
        }
    }

    /**
     * @param $method
     * @param string $endpoint
     * @param null $data
     * @return array|mixed
     */
    protected function sendRequest($method, $endpoint = "", $data = null)
    {
        $this->showDebug($data, 'Data');

        // *
        $serialized = [
            'typeRequest' => 'json',
            'bodyRequest' => json_encode($data),
        ];

        $serialized = http_build_query($serialized);
        $url = $this->endpoint . $endpoint;

        $this->showDebug($url, 'Url');
        $this->showDebug($method, 'Method');

        $response = $this->httpClient->request(
            $method,
            $url,
            // 'Authorization' => 'Bearer ' . $this->getApiKey()
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            // * data
            $serialized
        );

        return $this->getResponseData($response);
    }


    // * Review[@PortmoneTeam]: (!) unify request way for all JSON-based operations
    // * some operations requires `typeRequest+bodyRequest` (application/x-www-form-urlencoded) envelope
    // * some operations requires raw JSON body (in my opinion this is the better way)

    /**
     * @param $method
     * @param string $endpoint
     * @param null $data
     * @return array|mixed
     */
    protected function sendRawJsonRequest($method, $endpoint = "", $data = null)
    {
        $this->showDebug($data, 'Raw JSON data');
        $serialized = json_encode($data);

        $url = $this->endpoint . $endpoint;
        $this->showDebug($url, 'Url');
        $this->showDebug($method, 'Method');

        $response = $this->httpClient->request(
            $method,
            $url,
            [],
            // * data
            $serialized
        );

        return $this->getResponseData($response);
    }

    /**
     * @param $response
     * @return array|mixed
     */
    protected function getResponseData($response)
    {
        // * body
        $body = $response->getBody();
        $data = (string)$body !== '' ? json_decode($body, true) : [];

        // * get headers
        $headers = $response->getHeaders();
        $this->showDebug($headers, 'Headers');

        // * reason
        $this->showDebug($response->getReasonPhrase(), 'Reason');

        // * handle response code
        $code = $response->getStatusCode();
        $this->showDebug($code, 'Code');

        // * on `302` - do redirect to url described in `Location` header
        if ($code == 302) {
            if (isset($headers['Location'])) {
                $location = is_array($headers['Location']) ? current($headers['Location']) : $headers['Location'];
//                var_dump($location);
                if (!empty($location)) {
                    $data['links']['paymentUrl'] = $location;
                }
            }
        }

        return $data;
    }

    /**
     * Add information from payment system to data available for next use
     * @param $data
     * @return null|array
     */
    protected function addRequestParametersToData($data)
    {
        if (!isset($this->parametersToPass)) {
            return null;
        }

        foreach ($this->parametersToPass as $key => $name) {
            $value = $this->httpRequest->request->get($key);

            // * fix
            $value = ($value==='null') ? null : $value;

            $data[$name] = $value;
        }

        return $data;
    }

    /**
     * get/set property handling methods
     * @param $name
     * @param $arguments
     * @return mixed|null
     */
    public function __call($name, $arguments)
    {
        if (!is_string($name) || strlen($name) <= 3) {
            return null;
        }

        // * split name
        $method = substr($name, 0, 3);
        // * tid -> parameter real name
        $parameter = $this->tid2name(substr($name, 3));
//        var_dump($method);
//        var_dump($parameter);
//        var_dump($arguments);

        if (in_array($parameter, $this->coreParameters)) {
            if ($method == 'get') {
//                print_r("get :: result :: ");
//                print_r($this->getParameter($parameter));
//                print_r("\r\n\r\n");
                return $this->getParameter($parameter);
            } elseif ($method == 'set' && !empty($arguments) && isset($arguments[0])) {
                return $this->setParameter($parameter, $arguments[0]);
            }
        }

        return null;
    }


    /**
     * format parameter name from text ID
     * @param $tid
     * @return string
     */
    private function tid2name($tid)
    {
        return strtolower(substr($tid, 0, 1)) . substr($tid, 1);
    }
}
