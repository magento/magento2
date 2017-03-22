<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Framework\HTTP\ZendClient;
use Magento\Config\Model\Config;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\Store;

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
        if ($this->analyticsToken->isTokenExist()) {
            $result = (bool)$this->httpClient->request(
                ZendClient::POST,
                $this->config->getConfigDataValue($this->notifyDataChangedUrlPath),
                [
                    "access-token" => $this->analyticsToken->getToken(),
                    "url" => $this->config->getConfigDataValue(
                        Store::XML_PATH_SECURE_BASE_URL
                    ),
                ]
            );
        }
        return $result;
    }
}
