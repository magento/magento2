<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\AnalyticsConnector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Config\Model\Config;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\HTTP\ZendClient;
use Psr\Log\LoggerInterface;
use Magento\Analytics\Model\AnalyticsApiUserProvider;
use Magento\Analytics\Model\TokenGenerator;
use Magento\Store\Model\Store;

class SignUpCommand implements AnalyticsCommandInterface
{
    const MA_SIGNUP_URL_PATH = 'analytics/url/signup';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AnalyticsApiUserProvider
     */
    private $analyticsApiUserProvider;

    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;

    /**
     * SignUpCommand constructor.
     * @param Config $config
     * @param ZendClientFactory $zendClientFactory
     * @param AnalyticsToken $analyticsToken
     * @param LoggerInterface $logger
     * @param AnalyticsApiUserProvider $analyticsApiUserProvider
     * @param TokenGenerator $tokenGenerator
     */
    public function __construct(
        Config $config,
        ZendClientFactory $zendClientFactory,
        AnalyticsToken $analyticsToken,
        LoggerInterface $logger,
        AnalyticsApiUserProvider $analyticsApiUserProvider,
        TokenGenerator $tokenGenerator
    ) {
        $this->config = $config;
        $this->httpClientFactory = $zendClientFactory;
        $this->analyticsToken = $analyticsToken;
        $this->logger = $logger;
        $this->analyticsApiUserProvider = $analyticsApiUserProvider;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * This method execute sign-up command and send request to MA api
     * @return bool
     */
    public function execute()
    {
        $apiUserToken = $this->getApiUserToken();
        if (!$apiUserToken) {
            $this->logger->warning("The attempt of subscription was unsuccessful on step generate token.");
            return false;
        }
        
        $requestData = json_encode(
            [
                "token" => $apiUserToken,
                "url" => $this->config->getConfigDataValue(Store::XML_PATH_UNSECURE_BASE_URL)
            ]
        );

        $token = $this->getMAToken($requestData);
        if (!$token) {
            $this->logger->warning("The attempt of subscription was unsuccessful on step sign-up.");
            return false;
        }

        $this->analyticsToken->setToken($token);
        return true;
    }

    /**
     * Get MA Token from MA Api
     * @param string $requestData
     * @return bool|string
     */
    private function getMAToken($requestData)
    {
        $maEndPoint = $this->config->getConfigDataValue(self::MA_SIGNUP_URL_PATH);
        /** @var ZendClient $httpClient */
        $httpClient = $this->httpClientFactory->create();
        $httpClient->setUri($maEndPoint);
        $httpClient->setRawData($requestData);
        $httpClient->setMethod(\Zend_Http_Client::POST);
        try {
            $response = $httpClient->request();
            if ($response->getStatus() === 200) {
                $body = json_decode($response->getBody(), 1);
                if (isset($body['token']) && !empty($body['token'])) {
                    return $body['token'];
                }
            }
        } catch (\Zend_Http_Client_Exception $e) {
            $this->logger->critical($e);
        }
        return false;
    }
    
    /**
     * @return string|false
     */
    private function getApiUserToken()
    {
        $apiUserToken = $this->analyticsApiUserProvider->getToken();
        if (!$apiUserToken) {
            $this->tokenGenerator->execute();
            $apiUserToken = $this->analyticsApiUserProvider->getToken();
        }
        return $apiUserToken;
    }
}
