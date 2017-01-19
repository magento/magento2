<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model\AnalyticsConnector;

use Magento\Config\Model\Config;
use Magento\Framework\HTTP\ZendClientFactory as HttpClientFactory;
use Magento\Framework\HTTP\ZendClient as HttpClient;
use Zend_Http_Response as HttpResponse;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Class SignUpRequest
 */
class SignUpRequest
{
    /**
     * @var string
     */
    private $signUpUrlPath = 'analytics/url/signup';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var HttpClientFactory
     */
    private $httpClientFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SignUpRequest constructor.
     *
     * @param Config $config
     * @param HttpClientFactory $httpClientFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        HttpClientFactory $httpClientFactory,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->httpClientFactory = $httpClientFactory;
        $this->logger = $logger;
    }

    /**
     * Prepares json string with request data
     *
     * @param string $integrationToken
     * @return string
     */
    private function getRequestJson($integrationToken)
    {
        return json_encode(
            [
                "token" => $integrationToken,
                "url" => $this->config->getConfigDataValue(Store::XML_PATH_UNSECURE_BASE_URL)
            ]
        );
    }

    /**
     * Extracts token from the response
     *
     * @param HttpResponse $response
     * @return string|false
     */
    private function extractResponseToken(HttpResponse $response)
    {
        $token = false;
        if ($response->getStatus() === 200) {
            $body = json_decode($response->getBody(), 1);
            if (isset($body['token']) && !empty($body['token'])) {
                $token = $body['token'];
            }
        }
        return $token;
    }

    /**
     * Performs signUp call to MA
     * Sends data about instance base url and integration user token
     * Returns MA access token as a result
     *
     * @param string $integrationToken
     * @return string|false
     */
    public function call($integrationToken)
    {
        $token = false;
        /** @var HttpClient $httpClient */
        $httpClient = $this->httpClientFactory->create();
        $httpClient->setUri($this->config->getConfigDataValue($this->signUpUrlPath));
        $httpClient->setRawData($this->getRequestJson($integrationToken));
        $httpClient->setMethod(HttpClient::POST);
        try {
            $token = $this->extractResponseToken($httpClient->request());
            if (!$token) {
                $this->logger->warning('The attempt of subscription was unsuccessful on step sign-up.');
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $token;
    }
}
