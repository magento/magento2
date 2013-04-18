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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Store_StorageFactory
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Default storage class name
     *
     * @var string
     */
    protected $_defaultStorageClassName;

    /**
     * Installed storage class name
     *
     * @var string
     */
    protected $_installedStoreClassName;

    /**
     * @var Mage_Core_Model_Store_StorageInterface[]
     */
    protected $_cache = array();

    /**
     * @var Mage_Core_Model_Event_Manager
     */
    protected $_eventManager;

    /**
     * @var Mage_Core_Model_Logger
     */
    protected $_log;

    /**
     * @var Mage_Core_Model_ConfigInterface
     */
    protected $_config;

    /**
     * @var Mage_Core_Model_App_Proxy
     */
    protected $_app;

    /**
     * @var Mage_Core_Model_App_State
     */
    protected $_appState;

    /**
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Core_Model_Logger $log
     * @param Mage_Core_Model_ConfigInterface $config
     * @param Mage_Core_Model_App_Proxy $app
     * @param Mage_Core_Model_App_State $appState
     * @param string $defaultStorageClassName
     * @param string $installedStoreClassName
     */
    public function __construct(
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Core_Model_Logger $log,
        Mage_Core_Model_ConfigInterface $config,
        Mage_Core_Model_App_Proxy $app,
        Mage_Core_Model_App_State $appState,
        $defaultStorageClassName = 'Mage_Core_Model_Store_Storage_Default',
        $installedStoreClassName = 'Mage_Core_Model_Store_Storage_Db'
    ) {
        $this->_objectManager = $objectManager;
        $this->_defaultStorageClassName = $defaultStorageClassName;
        $this->_installedStoreClassName = $installedStoreClassName;
        $this->_eventManager = $eventManager;
        $this->_log = $log;
        $this->_appState = $appState;
        $this->_config = $config;
        $this->_app = $app;
    }

    /**
     * Get storage instance
     *
     * @param array $arguments
     * @return Mage_Core_Model_Store_StorageInterface
     * @throws InvalidArgumentException
     */
    public function get(array $arguments = array())
    {
        $className = $this->_appState->isInstalled() ?
            $this->_installedStoreClassName :
            $this->_defaultStorageClassName;

        if (false == isset($this->_cache[$className])) {
            /** @var $instance Mage_Core_Model_Store_StorageInterface */
            $instance = $this->_objectManager->create($className, $arguments);

            if (false === ($instance instanceof Mage_Core_Model_Store_StorageInterface)) {
                throw new InvalidArgumentException($className
                        . ' doesn\'t implement Mage_Core_Model_Store_StorageInterface'
                );
            }
            $this->_cache[$className] = $instance;
            $instance->initCurrentStore();
            if ($className === $this->_installedStoreClassName) {
                $useSid = $instance->getStore()->getConfig(Mage_Core_Model_Session_Abstract::XML_PATH_USE_FRONTEND_SID);
                $this->_app->setUseSessionInUrl($useSid);

                $this->_eventManager->dispatch('core_app_init_current_store_after');

                $this->_log->initForStore($instance->getStore(true), $this->_config);
            }
        }
        return $this->_cache[$className];
    }
}
