<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\HTTP\ZendClient;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateCommand
 * Command executes in case change store url
 * @since 2.2.0
 */
class UpdateCommand implements CommandInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    private $updateUrlPath = 'analytics/url/update';

    /**
     * @var AnalyticsToken
     * @since 2.2.0
     */
    private $analyticsToken;

    /**
     * @var Http\ClientInterface
     * @since 2.2.0
     */
    private $httpClient;

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $config;

    /**
     * @var LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * @var FlagManager
     * @since 2.2.0
     */
    private $flagManager;

    /**
     * @var ResponseResolver
     * @since 2.2.0
     */
    private $responseResolver;

    /**
     * @param AnalyticsToken $analyticsToken
     * @param Http\ClientInterface $httpClient
     * @param ScopeConfigInterface $config
     * @param LoggerInterface $logger
     * @param FlagManager $flagManager
     * @param ResponseResolver $responseResolver
     * @since 2.2.0
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        Http\ClientInterface $httpClient,
        ScopeConfigInterface $config,
        LoggerInterface $logger,
        FlagManager $flagManager,
        ResponseResolver $responseResolver
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->logger = $logger;
        $this->flagManager = $flagManager;
        $this->responseResolver = $responseResolver;
    }

    /**
     * Executes update request to MBI api in case store url was changed
     *
     * @return bool
     * @since 2.2.0
     */
    public function execute()
    {
        $result = false;
        if ($this->analyticsToken->isTokenExist()) {
            $response = $this->httpClient->request(
                ZendClient::PUT,
                $this->config->getValue($this->updateUrlPath),
                [
                    "url" => $this->flagManager
                        ->getFlagData(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE),
                    "new-url" => $this->config->getValue(Store::XML_PATH_SECURE_BASE_URL),
                    "access-token" => $this->analyticsToken->getToken(),
                ]
            );
            $result = $this->responseResolver->getResult($response);
            if (!$result) {
                $this->logger->warning(
                    sprintf(
                        'Update of the subscription for MBI service has been failed: %s',
                        !empty($response->getBody()) ? $response->getBody() : 'Response body is empty.'
                    )
                );
            }
        }

        return (bool)$result;
    }
}
