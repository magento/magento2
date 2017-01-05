<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model\AnalyticsConnector;

use Magento\Analytics\Setup\InstallData;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Config\Model\Config;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\App\Config\Storage\WriterInterface;

class SignUpCommand implements AnalyticsCommandInterface
{
    const MA_SIGNUP_URL_PATH = 'analytics/url/signup';

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface\
     */
    private $storeManager;

    /**
     * @var OauthServiceInterface
     */
    private $oauthService;

    /**
     * @var ZendClientFactory
     */
    private $httpClientFactory;

    /**
     * #@var WriterInterface
     */
    private $configWriter;

    /**
     * SignUpCommand constructor.
     * @param IntegrationServiceInterface $integrationService
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param OauthServiceInterface $oauthService
     * @param ZendClientFactory $zendClientFactory
     * @param WriterInterface $configWriter
     */
    public function __construct(
        IntegrationServiceInterface $integrationService,
        Config $config,
        StoreManagerInterface $storeManager,
        OauthServiceInterface $oauthService,
        ZendClientFactory $zendClientFactory,
        WriterInterface $configWriter
    ) {
        $this->integrationService = $integrationService;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->oauthService = $oauthService;
        $this->httpClientFactory = $zendClientFactory;
        $this->configWriter = $configWriter;
    }

    /**
     * This method execute sign-up command
     * 1) Generate or get exists access token for MA integration
     * 2) Get base URL
     * 3) Send data to MA service
     * 4) Save ma token to Magento config
     * @return bool
     */
    public function execute()
    {
        $integration = $this->integrationService
            ->findByName(
                $this->config->getConfigDataValue(InstallData::MAGENTO_API_USER_NAME_PATH)
            );
        $store = $this->storeManager->getStore();
        echo $this->oauthService->getAccessToken($integration->getConsumerId())->getToken();
        $requestData = json_encode(
            [
                "token" => $this->oauthService->getAccessToken($integration->getConsumerId())->getToken(),
                "url" => $store->getBaseUrl()
            ]
        );

        $maEndpoint = $this->config->getConfigDataValue(self::MA_SIGNUP_URL_PATH);
        /** @var ZendClient $httpClient */
        $httpClient = $this->httpClientFactory->create();
        $httpClient->setUri($maEndpoint);
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
        } catch (\Exception $e) {

        }
        return false;
    }

    /**
     * Save token to Magento config
     * @param string $token
     * @return void
     */
    private function saveToken($token)
    {
        $this->configWriter->save('analytics/ma/token', $token);
    }

}
