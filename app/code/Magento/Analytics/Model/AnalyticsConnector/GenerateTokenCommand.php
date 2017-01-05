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
use Magento\Integration\Model\ResourceModel\Integration as IntegratedResourceModel;

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
     * @var IntegratedResourceModel
     */
    private $integrationResourceModel;

    /**
     * GenerateTokenCommand constructor.
     * @param IntegrationServiceInterface $integrationService
     * @param Config $config
     * @param OauthServiceInterface $oauthService
     * @param IntegratedResourceModel $integrationResourceModel
     */
    public function __construct(
        IntegrationServiceInterface $integrationService,
        Config $config,
        OauthServiceInterface $oauthService,
        IntegratedResourceModel $integrationResourceModel
    ) {
        $this->integrationService = $integrationService;
        $this->config = $config;
        $this->oauthService = $oauthService;
        $this->integrationResourceModel = $integrationResourceModel;
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

        $creationResult = $this->oauthService->createAccessToken($integration->getConsumerId(), true);
        if ($creationResult === true) {
            $integration->setStatus(Integration::STATUS_ACTIVE);
            $this->integrationResourceModel->save($integration);
            return true;
        }
        return false;
    }
}
