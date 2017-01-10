<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * API Integration config
     *
     * @var IntegrationConfig
     */
    protected $integrationConfig;

    /**
     * Construct Setup plugin instance
     *
     * @param IntegrationAuthorizationInterface $integrationAuthorizationService
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param IntegrationConfig $integrationConfig
     */
    public function __construct(
        IntegrationAuthorizationInterface $integrationAuthorizationService,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        IntegrationConfig $integrationConfig
    ) {
        $this->integrationAuthorizationService = $integrationAuthorizationService;
        $this->_integrationService = $integrationService;
        $this->integrationConfig = $integrationConfig;
    }

    /**
     * Process integration resource permissions after the integration is created
     *
     * @param ConfigBasedIntegrationManager $subject
     * @param string[] $integrationNames Name of integrations passed as array from the invocation chain
     *
     * @return string[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated
     */
    public function afterProcessIntegrationConfig(
        ConfigBasedIntegrationManager $subject,
        $integrationNames
    ) {
        if (empty($integrationNames)) {
            return [];
        }
        /** @var array $integrations */
        $integrations = $this->integrationConfig->getIntegrations();
        foreach ($integrationNames as $name) {
            if (isset($integrations[$name])) {
                $integration = $this->_integrationService->findByName($name);
                if ($integration->getId()) {
                    $this->integrationAuthorizationService->grantPermissions(
                        $integration->getId(),
                        $integrations[$name]['resource']
                    );
                }
            }
        }
        return $integrationNames;
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
    public function afterProcessConfigBasedIntegrations(
        ConfigBasedIntegrationManager $subject,
        $integrations
    ) {
        if (empty($integrations)) {
            return [];
        }

        foreach (array_keys($integrations) as $name) {
            $integration = $this->_integrationService->findByName($name);
            if ($integration->getId()) {
                $this->integrationAuthorizationService->grantPermissions(
                    $integration->getId(),
                    $integrations[$name]['resource']
                );
            }
        }
        return $integrations;
    }
}
