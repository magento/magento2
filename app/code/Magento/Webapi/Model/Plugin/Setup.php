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
namespace Magento\Webapi\Model\Plugin;

use Magento\Authz\Model\UserIdentifier;
use Magento\Integration\Model\Integration;
use Magento\Webapi\Model\IntegrationConfig;

/**
 * Plugin for Magento\Framework\Module\Setup model to manage resource permissions of
 * integration installed from config file
 */
class Setup
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
     * @var \Magento\Integration\Service\V1\IntegrationInterface
     */
    protected $_integrationService;

    /**
     * Authorization service
     *
     * @var \Magento\Authz\Service\AuthorizationV1
     */
    protected $_authzService;

    /**
     * Factory to create UserIdentifier
     *
     * @var \Magento\Authz\Model\UserIdentifier\Factory
     */
    protected $_userIdentifierFactory;

    /**
     * Construct Setup plugin instance
     *
     * @param IntegrationConfig $integrationConfig
     * @param \Magento\Authz\Service\AuthorizationV1 $authzService
     * @param \Magento\Integration\Service\V1\IntegrationInterface $integrationService
     * @param \Magento\Authz\Model\UserIdentifier\Factory $userIdentifierFactory
     */
    public function __construct(
        IntegrationConfig $integrationConfig,
        \Magento\Authz\Service\AuthorizationV1 $authzService,
        \Magento\Integration\Service\V1\IntegrationInterface $integrationService,
        \Magento\Authz\Model\UserIdentifier\Factory $userIdentifierFactory
    ) {
        $this->_integrationConfig = $integrationConfig;
        $this->_authzService = $authzService;
        $this->_integrationService = $integrationService;
        $this->_userIdentifierFactory = $userIdentifierFactory;
    }

    /**
     * Process integration resource permissions after the integration is created
     *
     * @param \Magento\Integration\Model\Resource\Setup $subject
     * @param string[] $integrationNames Name of integrations passed as array from the invocation chain
     *
     * @return string[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterInitIntegrationProcessing(
        \Magento\Integration\Model\Resource\Setup $subject,
        $integrationNames
    ) {
        if (empty($integrationNames)) {
            return array();
        }
        /** @var array $integrations */
        $integrations = $this->_integrationConfig->getIntegrations();
        foreach ($integrationNames as $name) {
            if (isset($integrations[$name])) {
                $integration = $this->_integrationService->findByName($name);
                if ($integration->getId()) {
                    $userIdentifier = $this->_userIdentifierFactory->create(
                        UserIdentifier::USER_TYPE_INTEGRATION,
                        $integration->getId()
                    );
                    $this->_authzService->grantPermissions($userIdentifier, $integrations[$name]['resources']);
                }
            }
        }
        return $integrationNames;
    }
}
