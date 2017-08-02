<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model;

use Magento\Integration\Model\Config\Converter;
use Magento\Authorization\Model\Acl\AclRetriever;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class to manage integrations installed from config file
 *
 * @since 2.0.0
 */
class ConfigBasedIntegrationManager
{
    /**
     * Integration service
     *
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     * @since 2.1.0
     */
    protected $integrationService;

    /**
     * @var  AclRetriever
     * @since 2.1.0
     */
    protected $aclRetriever;

    /**
     * Integration config
     *
     * @var Config
     * @since 2.1.0
     */
    protected $integrationConfig;

    /**
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param AclRetriever $aclRetriever
     * @param Config $integrationConfig
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        AclRetriever $aclRetriever,
        Config $integrationConfig
    ) {
        $this->integrationService = $integrationService;
        $this->aclRetriever = $aclRetriever;
        $this->integrationConfig = $integrationConfig;
    }

    /**
     * Process integrations from config files for the given array of integration names
     *
     * @param array $integrationNames
     * @return array
     * @deprecated 2.1.0
     * @since 2.0.0
     */
    public function processIntegrationConfig(array $integrationNames)
    {
        if (empty($integrationNames)) {
            return [];
        }
        /** @var array $integrations */
        $integrations = $this->integrationConfig->getIntegrations();
        foreach ($integrationNames as $name) {
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
            $integrationData[Integration::SETUP_TYPE] = Integration::TYPE_CONFIG;
            // If it already exists, update it
            $integration = $this->integrationService->findByName($name);
            if ($integration->getId()) {
                //If Integration already exists, update it.
                //For now we will just overwrite the integration with same name but we will need a long term solution
                $integrationData[Integration::ID] = $integration->getId();
                $this->integrationService->update($integrationData);
            } else {
                $this->integrationService->create($integrationData);
            }
        }
        return $integrationNames;
    }

    /**
     * Process integrations from config files for the given array of integration names
     *  to be used with consolidated integration config
     *
     * @param array $integrations
     * @return array
     * @since 2.1.0
     */
    public function processConfigBasedIntegrations(array $integrations)
    {
        if (empty($integrations)) {
            return [];
        }

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
            if (isset($integrationDetails[$name]['resource'])) {
                $integrationData['resource'] = $integrationDetails[$name]['resource'];
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
     * @since 2.1.0
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
