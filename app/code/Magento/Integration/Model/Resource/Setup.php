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
namespace Magento\Integration\Model\Resource;

use Magento\Integration\Model\Manager;

/**
 * Resource Setup Model
 */
class Setup extends \Magento\Framework\Module\Setup
{
    /**
     * @var  Manager
     */
    protected $_integrationManager;

    /**
     * Construct resource Setup Model
     *
     * @param \Magento\Framework\Module\Setup\Context $context
     * @param string $resourceName
     * @param Manager $integrationManager
     * @param string $moduleName
     * @param string $connectionName
     *
     */
    public function __construct(
        \Magento\Framework\Module\Setup\Context $context,
        $resourceName,
        Manager $integrationManager,
        $moduleName = 'Magento_Integration',
        $connectionName = \Magento\Framework\Module\Updater\SetupInterface::DEFAULT_SETUP_CONNECTION
    ) {
        $this->_integrationManager = $integrationManager;
        parent::__construct($context, $resourceName, $moduleName, $connectionName);
    }

    /**
     * Initiate integration processing
     *
     * @param array $integrationNames
     * @return array of integration names sent to the next invocation
     */
    public function initIntegrationProcessing(array $integrationNames)
    {
        $this->_integrationManager->processIntegrationConfig($integrationNames);
        return $integrationNames;
    }
}
