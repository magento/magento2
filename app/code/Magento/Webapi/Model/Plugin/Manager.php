<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Plugin;

use Magento\Integration\Model\ConfigBasedIntegrationManager;
use Magento\Integration\Api\AuthorizationServiceInterface as IntegrationAuthorizationInterface;

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
     * Construct Setup plugin instance
     *
     * @param IntegrationAuthorizationInterface $integrationAuthorizationService
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     */
    public function __construct(
        IntegrationAuthorizationInterface $integrationAuthorizationService,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService
    ) {
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
