<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Client;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Signifyd\Model\Config;

/**
 * Class HttpClientFactory
 * Creates and configures HTTP client for RequestBuilder
 */
class HttpClientFactory
{
    /**
     * Specifies basic HTTP access authentication Header.
     *
     * @var string
     */
    private static $authorizationType = 'Authorization';

    /**
     * JSON HTTP Content-Type Header.
     *
     * @var string
     */
    private static $jsonDataType = 'application/json';

    /**
     * @var string
     */
    private static $urlSeparator = '/';

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
     * HttpClientCreator constructor.
     *
     * @param Config $config
     * @param ZendClientFactory $clientFactory
     * @param EncoderInterface $dataEncoder
     */
    public function __construct(
        Config $config,
        ZendClientFactory $clientFactory,
        EncoderInterface $dataEncoder
    ) {
        $this->config = $config;
        $this->clientFactory = $clientFactory;
        $this->dataEncoder = $dataEncoder;
    }

    /**
     * Creates and configures HTTP client.
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @param int|null $storeId
     * @return ZendClient
     * @throws \Zend_Http_Client_Exception
     */
    public function create($url, $method, array $params = [], $storeId = null)
    {
        $apiKey = $this->getApiKey($storeId);
        $apiUrl = $this->buildFullApiUrl($url, $storeId);

        $client = $this->createNewClient();
        $client->setHeaders(
            self::$authorizationType,
            sprintf('Basic %s', base64_encode($apiKey))
        );
        if (!empty($params)) {
            $encodedData = $this->dataEncoder->encode($params);
            $client->setRawData($encodedData, self::$jsonDataType);
        }
        $client->setMethod($method);
        $client->setUri($apiUrl);

        return $client;
    }

    /**
     * @return ZendClient
     */
    private function createNewClient()
    {
        return $this->clientFactory->create();
    }

    /**
     * Signifyd API key for merchant account.
     *
     * @see https://www.signifyd.com/docs/api/#/introduction/authentication
     * @param int|null $storeId
     * @return string
     */
    private function getApiKey($storeId)
    {
        return $this->config->getApiKey($storeId);
    }

    /**
     * Full URL for Singifyd API based on relative URL.
     *
     * @param string $url
     * @param int|null $storeId
     * @return string
     */
    private function buildFullApiUrl($url, $storeId)
    {
        $baseApiUrl = $this->getBaseApiUrl($storeId);
        $fullUrl = $baseApiUrl . self::$urlSeparator . ltrim($url, self::$urlSeparator);

        return $fullUrl;
    }

    /**
     * Base Sigifyd API URL without trailing slash.
     *
     * @param int|null $storeId
     * @return string
     */
    private function getBaseApiUrl($storeId)
    {
        $baseApiUrl = $this->config->getApiUrl($storeId);

        return rtrim($baseApiUrl, self::$urlSeparator);
    }
}
