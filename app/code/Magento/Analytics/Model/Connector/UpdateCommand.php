<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Plugin\BaseUrlConfigPlugin;
use Magento\Config\Model\Config;
use Magento\Framework\FlagManager;
use Magento\Framework\HTTP\ZendClient;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateCommand
 * Command executes in case change store url
 */
class UpdateCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $updateUrlPath = 'analytics/url/update';

    /**
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * @var Http\ClientInterface
     */
    private $httpClient;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @param AnalyticsToken $analyticsToken
     * @param Http\ClientInterface $httpClient
     * @param Config $config
     * @param LoggerInterface $logger
     * @param FlagManager $flagManager
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        Http\ClientInterface $httpClient,
        Config $config,
        LoggerInterface $logger,
        FlagManager $flagManager
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->logger = $logger;
        $this->flagManager = $flagManager;
    }

    /**
     * Executes update request to MBI api in case store url was changed
     * @return bool
     */
    public function execute()
    {
        $result = false;
        try {
            if ($this->analyticsToken->isTokenExist()) {
                $response = $this->httpClient->request(
                    ZendClient::PUT,
                    $this->config->getConfigDataValue($this->updateUrlPath),
                    $this->getRequestJson(),
                    ['Content-Type: application/json']
                );

                if ($response) {
                    $result = $response->getStatus() === 201;
                    if (!$result) {
                        $this->logger->warning(
                            sprintf(
                                'Update of the subscription for MBI service has been failed: %s',
                                !empty($response->getBody()) ? $response->getBody() : 'Response body is empty.'
                            )
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $result;
    }

    /**
     * Prepares request data in JSON format.
     * @return string
     */
    private function getRequestJson()
    {
        return json_encode(
            [
                "url" => $this->flagManager->getFlagData(BaseUrlConfigPlugin::OLD_BASE_URL_FLAG_CODE),
                "new-url" => $this->config->getConfigDataValue(
                    Store::XML_PATH_SECURE_BASE_URL
                ),
                "access-token" => $this->analyticsToken->getToken(),
            ]
        );
    }
}
