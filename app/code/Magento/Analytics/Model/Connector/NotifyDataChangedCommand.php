<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Config\Model\Config;
use Magento\Framework\HTTP\ZendClient;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Command notifies MBI about that data collection was finished.
 */
class NotifyDataChangedCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $notifyDataChangedUrlPath = 'analytics/url/notify_data_changed';

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
     * NotifyDataChangedCommand constructor.
     * @param AnalyticsToken $analyticsToken
     * @param Http\ClientInterface $httpClient
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        Http\ClientInterface $httpClient,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Notify MBI about that data collection was finished
     * @return bool
     */
    public function execute()
    {
        $result = false;
        try {
            if ($this->analyticsToken->isTokenExist()) {
                $this->httpClient->request(
                    ZendClient::POST,
                    $this->config->getConfigDataValue($this->notifyDataChangedUrlPath),
                    $this->getRequestJson(),
                    ['Content-Type: application/json']
                );
                $result = true;
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
                "access-token" => $this->analyticsToken->getToken(),
                "url" => $this->config->getConfigDataValue(
                    Store::XML_PATH_SECURE_BASE_URL
                ),
            ]
        );
    }
}
