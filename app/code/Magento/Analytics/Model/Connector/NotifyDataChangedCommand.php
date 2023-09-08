<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

use Laminas\Http\Request;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
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
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var ResponseResolver
     */
    private $responseResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * NotifyDataChangedCommand constructor.
     * @param AnalyticsToken $analyticsToken
     * @param Http\ClientInterface $httpClient
     * @param ScopeConfigInterface $config
     * @param ResponseResolver $responseResolver
     * @param LoggerInterface $logger
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        Http\ClientInterface $httpClient,
        ScopeConfigInterface $config,
        ResponseResolver $responseResolver,
        LoggerInterface $logger
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->responseResolver = $responseResolver;
        $this->logger = $logger;
    }

    /**
     * Notify MBI about that data collection was finished
     *
     * @return bool
     */
    public function execute()
    {
        $result = false;
        if ($this->analyticsToken->isTokenExist()) {
            $response = $this->httpClient->request(
                Request::METHOD_POST,
                $this->config->getValue($this->notifyDataChangedUrlPath),
                [
                    "access-token" => $this->analyticsToken->getToken(),
                    "url" => $this->config->getValue(Store::XML_PATH_SECURE_BASE_URL),
                ]
            );
            $result = $this->responseResolver->getResult($response);
        }
        return (bool)$result;
    }
}
