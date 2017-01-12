<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Config\Model\Config;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Integration\Model\Integration;

/**
 * Class AnalyticsApiUserProvider
 */
class AnalyticsApiUserProvider
{
    const MAGENTO_API_USER_NAME_PATH = 'analytics/integration_name';

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OauthServiceInterface
     */
    private $oauthService;

    /**
     * AnalyticsApiUserProvider constructor.
     * @param IntegrationServiceInterface $integrationService
     * @param Config $config
     * @param OauthServiceInterface $oauthService
     */
    public function __construct(
        IntegrationServiceInterface $integrationService,
        Config $config,
        OauthServiceInterface $oauthService
    ) {
        $this->integrationService = $integrationService;
        $this->config = $config;
        $this->oauthService = $oauthService;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        $consumerId = $this->getConsumerId();
        $accessToken = $this->oauthService->getAccessToken($consumerId);
        if ($accessToken) {
            return $accessToken->getToken();
        }
        return false;
    }

    /**
     * @return string
     */
    public function getConsumerId()
    {
        return $this->getIntegration()->getConsumerId();
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->getIntegration()->getData();
    }

    /**
     * @return Integration
     * @throws NotFoundException
     */
    private function getIntegration()
    {

        $integration = $this->integrationService->findByName(
            $this->config->getConfigDataValue(self::MAGENTO_API_USER_NAME_PATH)
        );
        if ($integration->getConsumerId() === null) {
            throw new NotFoundException(__('Api User not exist.'));
        }
        return $integration;
    }
}
