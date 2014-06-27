<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Model;

use Magento\Integration\Model\Integration;
use Magento\Integration\Model\Config\Converter;

/**
 * Class to manage integrations installed from config file
 *
 */
class Manager
{
    /**
     * Integration service
     *
     * @var \Magento\Integration\Service\V1\IntegrationInterface
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
     * @param \Magento\Integration\Service\V1\IntegrationInterface $integrationService
     */
    public function __construct(
        Config $integrationConfig,
        \Magento\Integration\Service\V1\IntegrationInterface $integrationService
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
            return array();
        }
        /** @var array $integrations */
        $integrations = $this->_integrationConfig->getIntegrations();
        foreach ($integrationNames as $name) {
            $integrationDetails = $integrations[$name];
            $integrationData = array(Integration::NAME => $name);
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
