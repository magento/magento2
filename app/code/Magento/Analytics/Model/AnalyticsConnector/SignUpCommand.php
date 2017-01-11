<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\AnalyticsConnector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Config\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\HTTP\ZendClient;
use Psr\Log\LoggerInterface;
use Magento\Analytics\Model\MagentoAnalyticsApiUser;
use Magento\Analytics\Model\TokenGenerator;

class SignUpCommand implements AnalyticsCommandInterface
{
    const MA_SIGNUP_URL_PATH = 'analytics/url/signup';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface\
     */
    private $storeManager;

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
     * @var MagentoAnalyticsApiUser
     */
    private $analyticsApiUser;
    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;

    /**
     * SignUpCommand constructor.
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param ZendClientFactory $zendClientFactory
     * @param AnalyticsToken $analyticsToken
     * @param LoggerInterface $logger
     * @param MagentoAnalyticsApiUser $analyticsApiUser
     * @param TokenGenerator $tokenGenerator
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager,
        ZendClientFactory $zendClientFactory,
        AnalyticsToken $analyticsToken,
        LoggerInterface $logger,
        MagentoAnalyticsApiUser $analyticsApiUser,
        TokenGenerator $tokenGenerator
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->httpClientFactory = $zendClientFactory;
        $this->analyticsToken = $analyticsToken;
        $this->logger = $logger;
        $this->analyticsApiUser = $analyticsApiUser;
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * This method execute sign-up command and send request to MA api
     * @return bool
     */
    public function execute()
    {
        $apiUsertoken = $this->analyticsApiUser->getToken();

        if (!$apiUsertoken) {
            $this->tokenGenerator->execute();
            $apiUsertoken = $this->analyticsApiUser->getToken();
        }

        if ($apiUsertoken) {
            $store = $this->storeManager->getStore();
            $requestData = json_encode(
                [
                    "token" => $apiUsertoken,
                    "url" => $store->getBaseUrl()
                ]
            );

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

                    if (isset($body['token'])) {
                        $this->saveToken($body['token']);
                    }
                    return true;
                }
            } catch (\Zend_Http_Client_Exception $e) {
                $this->logger->critical($e);
            }
        } else {
            $this->logger->warning("The attempt of subscription was unsuccessful on step generate token.");
        }
        $this->logger->warning("The attempt of subscription was unsuccessful on step sign-up.");
        return false;
    }

    /**
     * Save token to Magento config
     * @param string $token
     * @return void
     */
    private function saveToken($token)
    {
        $this->analyticsToken->setToken($token);
    }
}
