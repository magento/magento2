<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model;

use Magento\Integration\Model\Config\Converter;

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
    protected $_integrationService;

    /**
     * Integration config
     *
     * @var Config
     */
    protected $_integrationConfig;

    /**
     * @param Config $integrationConfig
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     */
    public function __construct(
        Config $integrationConfig,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService
    ) {
        $this->_integrationService = $integrationService;
        $this->_integrationConfig = $integrationConfig;
    }

    /**
     * Process integrations from config files for the given array of integration names
     *
     * @param array $integrationNames
     * @return array
     */
    public function processIntegrationConfig(array $integrationNames)
    {
        if (empty($integrationNames)) {
            return [];
        }
        /** @var array $integrations */
        $integrations = $this->_integrationConfig->getIntegrations();
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
            $integration = $this->_integrationService->findByName($name);
            if ($integration->getId()) {
                //If Integration already exists, update it.
                //For now we will just overwrite the integration with same name but we will need a long term solution
                $integrationData[Integration::ID] = $integration->getId();
                $this->_integrationService->update($integrationData);
            } else {
                $this->_integrationService->create($integrationData);
            }
        }
        return $integrationNames;
    }
}
