<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Plugin;

use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Integration\Api\AuthorizationServiceInterface as IntegrationAuthorizationInterface;
use Magento\Integration\Model\IntegrationConfig;

/**
 * Plugin for @see \Magento\Integration\Model\ConfigBasedIntegrationManager model to manage resource permissions of
 * integration installed from config file
 * @since 2.0.0
 */
class Manager
{
    /**
     * Integration service
     *
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     * @since 2.0.0
     */
    protected $_integrationService;

    /**
     * @var IntegrationAuthorizationInterface
     * @since 2.0.0
     */
    protected $integrationAuthorizationService;

    /**
     * API Integration config
     *
     * @var IntegrationConfig
     * @since 2.1.0
     */
    protected $integrationConfig;

    /**
     * Construct Setup plugin instance
     *
     * @param IntegrationAuthorizationInterface $integrationAuthorizationService
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param IntegrationConfig $integrationConfig
     * @since 2.0.0
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
     * @deprecated 2.1.0
     * @since 2.0.0
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
     * @since 2.1.0
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
