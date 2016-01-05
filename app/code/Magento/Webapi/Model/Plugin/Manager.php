<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Plugin;

use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Integration\Api\AuthorizationServiceInterface as IntegrationAuthorizationInterface;
use Magento\Integration\Model\IntegrationConfig;

/**
 * Plugin for ConfigBasedIntegrationManager model to manage resource permissions of
 * integration installed from config file
 */
class Manager
{
    /**
     * API Integration config
     *
     * @var IntegrationConfig
     */
    protected $_integrationConfig;

    /**
     * Integration service
     *
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     */
    protected $_integrationService;

    /**
     * @var IntegrationAuthorizationInterface
     */
    protected $integrationAuthorizationService;

    /**
     * Construct Setup plugin instance
     *
     * @param IntegrationConfig $integrationConfig
     * @param IntegrationAuthorizationInterface $integrationAuthorizationService
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     */
    public function __construct(
        IntegrationConfig $integrationConfig,
        IntegrationAuthorizationInterface $integrationAuthorizationService,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService
    ) {
        $this->_integrationConfig = $integrationConfig;
        $this->integrationAuthorizationService = $integrationAuthorizationService;
        $this->_integrationService = $integrationService;
    }

    /**
     * Process integration resource permissions after the integration is created
     *
     * @param ConfigBasedIntegrationManager $subject
     * @param array $integrations integrations passed as array from the invocation chain
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcessIntegrationConfig(
        ConfigBasedIntegrationManager $subject,
        $integrations
    ) {
        if (empty($integrations)) {
            return [];
        }
        /** @var array $integrationsResource */
        $integrationsResource = $this->_integrationConfig->getIntegrations();
        foreach (array_keys($integrations) as $name) {
            if (isset($integrationsResource[$name])) {
                $integration = $this->_integrationService->findByName($name);
                if ($integration->getId()) {
                    $this->integrationAuthorizationService->grantPermissions(
                        $integration->getId(),
                        $integrationsResource[$name]['resource']
                    );
                }
            }
        }
        return $integrations;
    }
}
