<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Config\Model\Config as SystemConfig;
use Magento\Integration\Model\Integration;
use Magento\Integration\Api\OauthServiceInterface;

/**
 * Class IntegrationManager
 *
 * Manages the integration user at magento side.
 * User name stored in config.
 * User roles
 * @since 2.2.0
 */
class IntegrationManager
{
    /**
     * @var SystemConfig
     * @since 2.2.0
     */
    private $config;

    /**
     * @var IntegrationServiceInterface
     * @since 2.2.0
     */
    private $integrationService;

    /**
     * @var OauthServiceInterface
     * @since 2.2.0
     */
    private $oauthService;

    /**
     * IntegrationManager constructor
     *
     * @param SystemConfig $config
     * @param IntegrationServiceInterface $integrationService
     * @param OauthServiceInterface $oauthService
     * @since 2.2.0
     */
    public function __construct(
        SystemConfig $config,
        IntegrationServiceInterface $integrationService,
        OauthServiceInterface $oauthService
    ) {
        $this->integrationService = $integrationService;
        $this->config = $config;
        $this->oauthService = $oauthService;
    }

    /**
     * Activate predefined integration user
     *
     * @return bool
     * @throws NoSuchEntityException
     * @since 2.2.0
     */
    public function activateIntegration()
    {
        $integration = $this->integrationService->findByName(
            $this->config->getConfigDataValue('analytics/integration_name')
        );
        if (!$integration->getId()) {
            throw new NoSuchEntityException(__('Cannot find predefined integration user!'));
        }
        $integrationData = $this->getIntegrationData(Integration::STATUS_ACTIVE);
        $integrationData['integration_id'] = $integration->getId();
        $this->integrationService->update($integrationData);
        return true;
    }

    /**
     * This method execute Generate Token command and enable integration
     *
     * @return bool|\Magento\Integration\Model\Oauth\Token
     * @since 2.2.0
     */
    public function generateToken()
    {
        $consumerId = $this->generateIntegration()->getConsumerId();
        $accessToken = $this->oauthService->getAccessToken($consumerId);
        if (!$accessToken && $this->oauthService->createAccessToken($consumerId, true)) {
            $accessToken = $this->oauthService->getAccessToken($consumerId);
        }
        return $accessToken;
    }

    /**
     * Returns consumer Id for MA integration user
     *
     * @return \Magento\Integration\Model\Integration
     * @since 2.2.0
     */
    private function generateIntegration()
    {
        $integration = $this->integrationService->findByName(
            $this->config->getConfigDataValue('analytics/integration_name')
        );
        if (!$integration->getId()) {
            $integration = $this->integrationService->create($this->getIntegrationData());
        }
        return $integration;
    }

    /**
     * Returns default attributes for MA integration user
     *
     * @param int $status
     * @return array
     * @since 2.2.0
     */
    private function getIntegrationData($status = Integration::STATUS_INACTIVE)
    {
        $integrationData = [
            'name' => $this->config->getConfigDataValue('analytics/integration_name'),
            'status' => $status,
            'all_resources' => false,
            'resource' => [
                'Magento_Analytics::analytics',
                'Magento_Analytics::analytics_api'
            ],
        ];
        return $integrationData;
    }
}
