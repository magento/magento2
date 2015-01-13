<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Model\Resource;

class Setup extends \Magento\Framework\Module\DataSetup
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\GoogleShopping\Model\ConfigFactory
     */
    protected $_configFactory;

    /**
     * @param \Magento\Framework\Module\Setup\Context $context
     * @param string $resourceName
     * @param \Magento\GoogleShopping\Model\ConfigFactory $configFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param string $moduleName
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Module\Setup\Context $context,
        $resourceName,
        \Magento\GoogleShopping\Model\ConfigFactory $configFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        $moduleName = 'Magento_GoogleShopping',
        $connectionName = \Magento\Framework\Module\Updater\SetupInterface::DEFAULT_SETUP_CONNECTION
    ) {
        $this->_configFactory = $configFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $resourceName, $moduleName, $connectionName);
    }

    /**
     * @return \Magento\Framework\Module\Manager
     */
    public function getModuleManager()
    {
        return $this->moduleManager;
    }
}
