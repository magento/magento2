<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model;

use Magento\Integration\Model\Config\Converter;
use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class to manage integrations installed from config file
 *
 */
class ConfigBasedIntegrationManager
{
    /**
     * Integration service
     *
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     */
    protected $integrationService;

    /**
     * Integration config
     *
     * @var IntegrationConfig
     */
    protected $integrationConfig;

    /**
     * @var  AclRetriever
     */
    protected $aclRetriever;

    /**
     * @param IntegrationConfig $integrationConfig
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param AclRetriever $aclRetriever
     */
    public function __construct(
        IntegrationConfig $integrationConfig,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        AclRetriever $aclRetriever
    ) {
        $this->integrationService = $integrationService;
        $this->integrationConfig = $integrationConfig;
        $this->aclRetriever = $aclRetriever;
    }

    /**
     * Process integrations from config files for the given array of integration names
     *
     * @param array $integrations
     * @return array
     */
    public function processIntegrationConfig(array $integrations)
    {
        if (empty($integrations)) {
            return [];
        }

        /** @var array $integrationsResource */
        $integrationsResource = $this->integrationConfig->getIntegrations();
        foreach (array_keys($integrations) as $name) {
            $integrationDetails = $integrations[$name];
            $integrationData = [Integration::NAME => $name];

            if (isset($integrationDetails[Converter::KEY_EMAIL])) {
                $integrationData[Integration::EMAIL] = $integrationDetails[Converter::KEY_EMAIL];
            }
            if (isset($integrationDetails[Converter::KEY_AUTHENTICATION_ENDPOINT_URL])) {
                $integrationData[Integration::ENDPOINT] =
                    $integrationDetails[Converter::KEY_AUTHENTICATION_ENDPOINT_URL];
            }
            if (isset($integrationDetails[Converter::KEY_IDENTITY_LINKING_URL])) {
                $integrationData[Integration::IDENTITY_LINK_URL] =
                    $integrationDetails[Converter::KEY_IDENTITY_LINKING_URL];
            }
            if (isset($integrationsResource[$name]['resource'])) {
                $integrationData['resource'] = $integrationsResource[$name]['resource'];
            }
            $integrationData[Integration::SETUP_TYPE] = Integration::TYPE_CONFIG;

            $integration = $this->integrationService->findByName($name);
            if ($integration->getId()) {
                $originalResources = $this->aclRetriever->getAllowedResourcesByUser(
                    UserContextInterface::USER_TYPE_INTEGRATION,
                    $integration->getId()
                );
                $updateData = $integrationData;
                $updateData[Integration::ID] = $integration->getId();
                $integration = $this->integrationService->update($updateData);

                // If there were any changes, delete then recreate integration
                if ($this->hasDataChanged($integration, $originalResources)) {
                    $this->integrationService->delete($integration->getId());
                    $integrationData[Integration::STATUS] = Integration::STATUS_RECREATED;
                } else {
                    continue;
                }
            }
            $this->integrationService->create($integrationData);
        }
        return $integrations;
    }

    /**
     * Check whether integration data or assigned resources were changed
     *
     * @param Integration $integration
     * @param array $originalResources
     * @return bool
     */
    private function hasDataChanged(Integration $integration, $originalResources)
    {
        if (!$integration->getOrigData()) {
            return true;
        }

        // Check if resources have changed
        $newResources = $integration->getData('resource');
        $commonResources = array_intersect(
            $originalResources,
            $newResources
        );

        if (count($commonResources) != count($originalResources) || count($commonResources) != count($newResources)) {
            return true;
        }

        // Check if other data has changed
        $fields = [
            Integration::ID,
            Integration::NAME,
            Integration::EMAIL,
            Integration::ENDPOINT,
            Integration::IDENTITY_LINK_URL,
            Integration::SETUP_TYPE,
            Integration::CONSUMER_ID
        ];
        foreach ($fields as $field) {
            if ($integration->getOrigData($field) != $integration->getData($field)) {
                return true;
            }
        }

        return false;
    }
}
