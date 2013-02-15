<?php
/**
 * Object Manager config
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
class Mage_Core_Model_ObjectManager_Config extends Mage_Core_Model_ObjectManager_ConfigAbstract
{
    /**
     * Initial configuration required to load main configuration
     *
     * @var array
     */
    protected $_initialConfig = array(
        'preference' => array(
            'Mage_Core_Model_Db_UpdaterInterface' => 'Mage_Core_Model_Db_Updater',
            'Mage_Core_Model_AppInterface' => 'Mage_Core_Model_App_Proxy',
            'Mage_Core_Model_Config_InvalidatorInterface' => 'Mage_Core_Model_Config_Invalidator_Proxy',
        ),
        'Mage_Core_Model_Cache' => array(
            'parameters' => array('config' => 'Mage_Core_Model_Config_Proxy')
        ),
        'Mage_Core_Model_Config' => array(
            'parameters' => array('storage' => 'Mage_Core_Model_Config_Storage')
        ),
        'Mage_Core_Model_Config_Container' => array(
            'parameters' => array('configCache' => 'Mage_Core_Model_Config_Cache_Proxy')
        ),
        'Mage_Core_Model_Config_Resource' => array(
            'parameters' => array('config' => 'Mage_Core_Model_Config_Primary')
        ),
        'Mage_Core_Model_Config_Locales' => array(
            'parameters' => array('storage' => 'Mage_Core_Model_Config_Storage_Locales')
        ),
        'Mage_Core_Model_Config_Modules' => array(
            'parameters' => array('storage' => 'Mage_Core_Model_Config_Storage_Modules')
        ),
        'Mage_Core_Model_Config_Storage' => array(
            'parameters' => array('loader' => 'Mage_Core_Model_Config_Loader_Proxy')
        ),
        'Mage_Core_Model_Config_Storage_Modules' => array(
            'parameters' => array('loader' => 'Mage_Core_Model_Config_Loader_Modules_Proxy')
        ),
        'Mage_Core_Model_Config_Storage_Locales' => array(
            'parameters' => array('loader' => 'Mage_Core_Model_Config_Loader_Locales_Proxy')
        ),
        'Mage_Core_Model_Event_Config' => array(
            'parameters' => array('config' => 'Mage_Core_Model_Config_Modules_Proxy')
        ),
        'Mage_Install_Model_Installer' => array(
            'parameters' => array('config' => 'Mage_Core_Model_Config_Proxy')
        ),
        'Mage_Core_Model_Config_Invalidator' => array(
            'parameters' => array(
                'primaryConfig' => 'Mage_Core_Model_Config_Primary',
                'modulesConfig' => 'Mage_Core_Model_Config_Modules',
                'localesConfig' => 'Mage_Core_Model_Config_Locales',
            )
        ),
        'Magento_Filesystem' => array(
            'parameters' => array(
                'adapter' => 'Magento_Filesystem_Adapter_Local'
            ),
            'shared' => 0
        )
    );

    /**
     * Configure object manager
     *
     * @param Magento_ObjectManager $objectManager
     */
    public function configure(Magento_ObjectManager $objectManager)
    {
        Magento_Profiler::start('initial');
        $objectManager->setConfiguration(array_replace_recursive(
            $this->_initialConfig,
            array(
                'Mage_Core_Model_Dir' => array(
                    'parameters' => array(
                        'baseDir' => $this->_getParam(Mage::PARAM_BASEDIR),
                        'uris' => $this->_getParam(MAGE::PARAM_APP_URIS, array()),
                        'dirs' => $this->_getParam(Mage::PARAM_APP_DIRS, array())
                    )
                ),
                'Mage_Core_Model_Config_Loader_Local' => array(
                    'parameters' => array(
                        'customFile' => $this->_getParam(Mage::PARAM_CUSTOM_LOCAL_FILE),
                        'customConfig' => $this->_getParam(Mage::PARAM_CUSTOM_LOCAL_CONFIG)
                    )
                ),
                'Mage_Core_Model_Config_Loader_Modules' => array(
                    'parameters' => array('allowedModules' => $this->_getParam(Mage::PARAM_ALLOWED_MODULES, array()))
                ),
                'Mage_Core_Model_Cache' => array(
                    'parameters' => array(
                        'options' => $this->_getParam(Mage::PARAM_CACHE_OPTIONS, array()),
                        'banCache' => $this->_getParam(Mage::PARAM_BAN_CACHE, false),
                    )
                ),
                'Mage_Core_Model_StoreManager' => array(
                    'parameters' => array(
                        'scopeCode' => $this->_getParam(Mage::PARAM_RUN_CODE, ''),
                        'scopeType' => $this->_getParam(Mage::PARAM_RUN_TYPE, 'store'),
                    )
                )
            )
        ));

        Magento_Profiler::start('primary_load');
        /** @var $config Mage_Core_Model_Config_Primary*/
        $config = $objectManager->get('Mage_Core_Model_Config_Primary');
        Magento_Profiler::stop('primary_load');
        $configurators = $config->getNode('global/configurators');
        if ($configurators) {
            $configurators = $configurators->asArray();
            if (count($configurators)) {
                foreach ($configurators as $configuratorClass) {
                    /** @var $configurator  Magento_ObjectManager_Configuration*/
                    $configurator = $objectManager->create($configuratorClass, array('params' => $this->_params));
                    $configurator->configure($objectManager);
                }
            }
        }
        Magento_Profiler::stop('initial');
        Magento_Profiler::start('global_primary');
        $diConfig = $config->getNode('global/di');
        if ($diConfig) {
            $objectManager->setConfiguration($diConfig->asArray());
        }
        Magento_Profiler::stop('global_primary');
    }
}
