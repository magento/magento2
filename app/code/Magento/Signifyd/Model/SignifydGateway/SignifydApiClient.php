<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway;

use Magento\Signifyd\Model\Config;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Json\DecoderInterface;

/**
 * Signifyd API Client.
 *
 * Encapsulates Signifyd API protocol.
 */
class SignifydApiClient
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ZendClientFactory
     */
    private $clientFactory;

    /**
     * @var EncoderInterface
     */
    private $dataEncoder;

    /**
     * @var DecoderInterface
     */
    private $dataDecoder;

    /**
     * SignifydApiClient constructor.
     *
     * Class uses client factory to instantiate new client for interacting with API.
     * All requests and responses are processed by JSON encoder and decoder.
     *
     * @aparm Config $config
     * @param ZendClientFactory $clientFactory
     * @param EncoderInterface $dataEncoder
     * @param DecoderInterface $dataDecoder
     */
    public function __construct(
        Config $config,
        ZendClientFactory $clientFactory,
        EncoderInterface $dataEncoder,
        DecoderInterface $dataDecoder
    ) {
        $this->config = $config;
        $this->clientFactory = $clientFactory;
        $this->dataEncoder = $dataEncoder;
        $this->dataDecoder = $dataDecoder;
    }

    /**
     * Perform call to Signifyd API.
     *
     * Method returns associative array that corresponds to successful result.
     * Current implementation do not expose details in case of failure.
     *
     * @param $url
     * @param $method
     * @param array $params
     * @return array
     * @throws SignifydApiCallException
     * @throws SignifydApiResponseException
     */
    public function makeApiCall($url, $method, array $params = [])
    {
        try {
            $response = $this->sendRequest($url, $method, $params);
        } catch (\Exception $e) {
            throw new SignifydApiCallException(
                'Unable to call Signifyd API: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        $result = $this->handleResponse($response);
        return $result;
    }

    /**
     * Send HTTP request to Signifyd API.
     *
     * @param $url
     * @param $method
     * @param array $params
     * @return \Zend_Http_Response
     * @throws SignifydApiCallException
     */
    private function sendRequest($url, $method, array $params = [])
    {
        $apiKey = $this->getApiKey();
        $apiUrl = $this->buildFullApiUrl($url);

        $client = $this->createNewClient();
        $client->setHeaders(
            'Authorization',
            sprintf('Basic %s', $apiKey)
        );
//        $client->setHeaders(
//            'Accept-encoding',
//            'identity'
//        );
        if (!empty($params)) {
            $encodedData = $this->dataEncoder->encode($params);
            $client->setRawData($encodedData, 'application/json');
        }
        $client->setMethod($method);
        $client->setUri($apiUrl);

        $response = $client->request();
        return $response;
    }

    /**
     * Read result of successful operation and throw exception in case of any failure.
     *
     * @param \Zend_Http_Response $response
     *
     * @return mixed
     * @throws SignifydApiCallException
     * @throws SignifydApiResponseException
     */
    private function handleResponse(\Zend_Http_Response $response)
    {
        $responseBody = $response->getBody();

        switch ($response->getStatus()) {
            case 200:
            case 201:
            case 204:
                try {
                    $decodedResponseBody = $this->dataDecoder->decode($responseBody);
                } catch (\Exception $e) {
                    throw new SignifydApiResponseException('Signifyd API response is not valid JSON.');
                }
                return $decodedResponseBody;
            case 400:
                throw new SignifydApiCallException(
                    'Bad Request - The request could not be parsed. Response: ' . $responseBody
                );
            case 404:
                throw new SignifydApiCallException(
                    'Not Found - resource does not exist. Response: ' . $responseBody
                );
            case 409:
                throw new SignifydApiCallException(
                    'Conflict - with state of the resource on server. Can occur with (too rapid) PUT requests.' .
                    'Response: ' . $responseBody
                );
            case 401:
                throw new SignifydApiCallException(
                    'Unauthorized - user is not logged in, could not be authenticated. Response: ' . $responseBody
                );
            case 403:
                throw new SignifydApiCallException(
                    'Forbidden - Cannot access resource. Response: ' . $responseBody
                );
            case 500:
                throw new SignifydApiCallException('Server error.');
            default:
                throw new SignifydApiResponseException(
                    sprintf('Unexpected Signifyd API response code "%s"', $response->getStatus())
                );
        }
    }

    /**
     * @return ZendClient
     */
    private function createNewClient()
    {
        return $this->clientFactory->create();
    }

    /**
     * Returns Signifyd API key for merchant account
     * @see https://www.signifyd.com/docs/api/#/introduction/authentication
     *
     * @return string
     */
    private function getApiKey()
    {
        return $this->config->getApiKey();
    }

    /**
     * Builds full URL for Singifyd API based on relative URL
     *
     * @param $url
     * @return string
     */
    private function buildFullApiUrl($url)
    {
        $baseApiUrl = $this->getBaseApiUrl();
        $fullUrl = $baseApiUrl . '/' . ltrim($url, '/');
        return $fullUrl;
    }

    /**
     * Returns Base Sigifyd API URL without trailing slash
     *
     * @return string
     */
    private function getBaseApiUrl()
    {
        $baseApiUrl = $this->config->getApiUrl();
        return rtrim($baseApiUrl, '/');
    }

}