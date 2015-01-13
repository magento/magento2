<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model\Plugin\Service\V1;

use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Integration\Model\Integration as IntegrationModel;
use Magento\Integration\Service\V1\AuthorizationServiceInterface as IntegrationAuthorizationInterface;

/**
 * Plugin for \Magento\Integration\Service\V1\Integration.
 */
class Integration
{
    /** @var IntegrationAuthorizationInterface */
    protected $integrationAuthorizationService;

    /** @var  AclRetriever */
    protected $aclRetriever;

    /**
     * Initialize dependencies.
     *
     * @param IntegrationAuthorizationInterface $integrationAuthorizationService
     * @param AclRetriever $aclRetriever
     */
    public function __construct(
        IntegrationAuthorizationInterface $integrationAuthorizationService,
        AclRetriever $aclRetriever
    ) {
        $this->integrationAuthorizationService = $integrationAuthorizationService;
        $this->aclRetriever  = $aclRetriever;
    }

    /**
     * Persist API permissions.
     *
     * @param \Magento\Integration\Service\V1\Integration $subject
     * @param IntegrationModel $integration
     *
     * @return IntegrationModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreate(\Magento\Integration\Service\V1\Integration $subject, $integration)
    {
        $this->_saveApiPermissions($integration);
        return $integration;
    }

    /**
     * Persist API permissions.
     *
     * @param \Magento\Integration\Service\V1\Integration $subject
     * @param IntegrationModel $integration
     *
     * @return IntegrationModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdate(\Magento\Integration\Service\V1\Integration $subject, $integration)
    {
        $this->_saveApiPermissions($integration);
        return $integration;
    }

    /**
     * Add API permissions to integration data.
     *
     * @param \Magento\Integration\Service\V1\Integration $subject
     * @param IntegrationModel $integration
     *
     * @return IntegrationModel
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(\Magento\Integration\Service\V1\Integration $subject, $integration)
    {
        $this->_addAllowedResources($integration);
        return $integration;
    }

    /**
     * Add the list of allowed resources to the integration object data by 'resource' key.
     *
     * @param IntegrationModel $integration
     * @return void
     */
    protected function _addAllowedResources(IntegrationModel $integration)
    {
        if ($integration->getId()) {
            $integration->setData(
                'resource',
                $this->aclRetriever->getAllowedResourcesByUser(
                    UserContextInterface::USER_TYPE_INTEGRATION,
                    (int)$integration->getId()
                )
            );
        }
    }

    /**
     * Persist API permissions.
     *
     * Permissions are expected to be set to integration object by 'resource' key.
     * If 'all_resources' is set and is evaluated to true, permissions to all resources will be granted.
     *
     * @param IntegrationModel $integration
     * @return void
     */
    protected function _saveApiPermissions(IntegrationModel $integration)
    {
        if ($integration->getId()) {
            if ($integration->getData('all_resources')) {
                $this->integrationAuthorizationService->grantAllPermissions($integration->getId());
            } elseif (is_array($integration->getData('resource'))) {
                $this->integrationAuthorizationService
                    ->grantPermissions($integration->getId(), $integration->getData('resource'));
            } else {
                $this->integrationAuthorizationService->grantPermissions($integration->getId(), []);
            }
        }
    }

    /**
     * Process integration resource permissions after the integration is created
     *
     * @param \Magento\Integration\Service\V1\Integration $subject
     * @param array $integrationData Data of integration deleted
     *
     * @return array $integrationData
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(\Magento\Integration\Service\V1\Integration $subject, array $integrationData)
    {
        //No check needed for integration data since it cannot be empty in the parent invocation - delete
        $integrationId = (int)$integrationData[IntegrationModel::ID];
        $this->integrationAuthorizationService->removePermissions($integrationId);
        return $integrationData;
    }
}
