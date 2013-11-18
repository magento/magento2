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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Service;

/**
 * Integration Service.
 * This service is used to interact with integrations.
 */
class IntegrationV1 implements \Magento\Integration\Service\IntegrationV1Interface
{
    /** @var \Magento\Integration\Model\Integration\Factory $_integrationFactory */
    private $_integrationFactory;

    /**
     * @param \Magento\Integration\Model\Integration\Factory $integrationFactory
     */
    public function __construct(\Magento\Integration\Model\Integration\Factory $integrationFactory)
    {
        $this->_integrationFactory = $integrationFactory;
    }

    /**
     * Create a new Integration
     *
     * @param array $integrationData
     * @return array Integration data
     * @throws \Magento\Integration\Exception
     */
    public function create(array $integrationData)
    {
        $this->_checkIntegrationByName($integrationData['name']);
        $integration = $this->_integrationFactory->create($integrationData);
        $this->_validateIntegration($integration);
        $integration->save();
        return $integration->getData();
    }

    /**
     * Update an Integration.
     *
     * @param array $integrationData
     * @return array Integration data
     * @throws \Magento\Integration\Exception
     */
    public function update(array $integrationData)
    {
        $integration = $this->_loadIntegrationById($integrationData['integration_id']);
        //If name has been updated check if it conflicts with an existing integration
        if ($integration->getName() != $integrationData['name']) {
            $this->_checkIntegrationByName($integrationData['name']);
        }
        $integration->addData($integrationData);
        $this->_validateIntegration($integration);
        $integration->save();
        return $integration->getData();
    }

    /**
     * Get the details of a specific Integration.
     *
     * @param int $integrationId
     * @return array Integration data
     * @throws \Magento\Integration\Exception
     */
    public function get($integrationId)
    {
        $integration = $this->_loadIntegrationById($integrationId);
        return $integration->getData();
    }

    /**
     * Validate an integration
     *
     * @param \Magento\Integration\Model\Integration $integration
     * @throws \Magento\Integration\Exception
     */
    private function _validateIntegration(\Magento\Integration\Model\Integration $integration)
    {
        if ($integration->getAuthentication() == \Magento\Integration\Model\Integration::AUTHENTICATION_OAUTH
            && !$integration->getEndpoint()
        ) {
            throw new \Magento\Integration\Exception(__('Please enter endpoint for oAuth.'));
        }
    }

    /**
     * Check if an integration exists by the name
     *
     * @param string $name
     * @throws \Magento\Integration\Exception
     */
    private function _checkIntegrationByName($name)
    {
        $integration = $this->_integrationFactory->create()->load($name, 'name');
        if ($integration->getId()) {
            throw new \Magento\Integration\Exception(__("Integration with name '%1' exists.", $name));
        }
    }

    /**
     * Load integration by id.
     *
     * @param int $integrationId
     * @return \Magento\Integration\Model\Integration
     * @throws \Magento\Integration\Exception
     */
    protected function _loadIntegrationById($integrationId)
    {
        $integration = $this->_integrationFactory->create()->load($integrationId);
        if (!$integration->getId()) {
            throw new \Magento\Integration\Exception(__("Integration with ID '%1' doesn't exist.", $integrationId));
        }
        return $integration;
    }
}
