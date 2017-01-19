<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Config\Model\Config;

/**
 * Class TokenProvider
 */
class TokenProvider
{
    /**
     * @var OauthServiceInterface
     */
    private $oauthService;

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var Config
     */
    private $config;

    /**
     * TokenProvider constructor.
     *
     * @param Config $config
     * @param OauthServiceInterface $oauthService
     * @param IntegrationServiceInterface $integrationService
     */
    public function __construct(
        Config $config,
        OauthServiceInterface $oauthService,
        IntegrationServiceInterface $integrationService
    ) {
        $this->config = $config;
        $this->oauthService = $oauthService;
        $this->integrationService = $integrationService;
    }

    /**
     * Returns consumer Id for MA integration user
     *
     * @return string
     */
    private function getIntegrationConsumerId()
    {
        $integration = $this->integrationService->findByName(
            $this->config->getConfigDataValue('analytics/integration_name')
        );
        return $integration->getConsumerId();
    }

    /**
     * This method execute Generate Token command and enable integration
     *
     * @return bool|string
     */
    public function getToken()
    {
        $consumerId = $this->getIntegrationConsumerId();
        $accessToken = $this->oauthService->getAccessToken($consumerId);
        if (!$accessToken && $this->oauthService->createAccessToken($consumerId, true)) {
            $accessToken = $this->oauthService->getAccessToken($consumerId);
        }
        return $accessToken;
    }
}
