<?php
/**
 * DB-stored application configuration loader
 *
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
class Mage_Core_Model_Config_Loader_Db implements Mage_Core_Model_Config_LoaderInterface
{
    /**
     * Modules configuration
     *
     * @var Mage_Core_Model_Config_Modules
     */
    protected $_config;

    /**
     * DB scheme model
     *
     * @var Mage_Core_Model_Db_UpdaterInterface
     */
    protected $_dbUpdater;

    /**
     * Resource model of config data
     *
     * @var Mage_Core_Model_Resource_Config
     */
    protected $_resource;

    /**
     * @var Mage_Core_Model_Config_BaseFactory
     */
    protected $_configFactory;

    /**
     * @param Mage_Core_Model_Config_Modules $modulesConfig
     * @param Mage_Core_Model_Resource_Config $resource
     * @param Mage_Core_Model_Db_UpdaterInterface $schemeUpdater
     * @param Mage_Core_Model_Config_BaseFactory $factory
     */
    public function __construct(
        Mage_Core_Model_Config_Modules $modulesConfig,
        Mage_Core_Model_Resource_Config $resource,
        Mage_Core_Model_Db_UpdaterInterface $schemeUpdater,
        Mage_Core_Model_Config_BaseFactory $factory
    ) {
        $this->_config = $modulesConfig;
        $this->_resource = $resource;
        $this->_dbUpdater = $schemeUpdater;
        $this->_configFactory = $factory;
    }

    /**
     * Populate configuration object
     *
     * @param Mage_Core_Model_Config_Base $config
     */
    public function load(Mage_Core_Model_Config_Base $config)
    {
        if (false == $this->_resource->getReadConnection()) {
            return;
        }

        //update database scheme
         $this->_dbUpdater->updateScheme();

        //apply modules configuration
        $config->extend($this->_configFactory->create($this->_config->getNode()));

        //load db configuration
        Magento_Profiler::start('load_db');
        $this->_resource->loadToXml($config);
        Magento_Profiler::stop('load_db');
    }
}
