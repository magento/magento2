<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model\AnalyticsConnector;

use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Config\Model\Config;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Integration;
use Magento\Analytics\Setup\InstallData;

/**
 * Class GenerateTokenCommand
 */
class GenerateTokenCommand implements AnalyticsCommandInterface
{
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
     * GenerateTokenCommand constructor.
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
     * This method execute Generate Token command and enable integration
     * @return bool
     */
    public function execute()
    {
        $integration = $this->integrationService
            ->findByName(
                $this->config->getConfigDataValue(InstallData::MAGENTO_API_USER_NAME_PATH)
            );
        $CreateTokenResult = $this->oauthService->createAccessToken($integration->getConsumerId());
        if ($CreateTokenResult) {
            $integration->setStatus(Integration::STATUS_ACTIVE);
            $this->integrationService->update($integration->getData());
            return true;
        }
        return false;
    }
}
