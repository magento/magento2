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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Implementation of the @magentoConfigFixture doc comment directive
 */
class Magento_Test_Listener_Annotation_Config
{
    /**
     * @var Magento_Test_Listener
     */
    protected static $_listenerDefault;

    /**
     * Whether the execution is happening between 'startTest' and 'stopTest'
     *
     * @var bool
     */
    protected static $_isWithinTest = false;

    /**
     * @var Magento_Test_Listener
     */
    protected $_listener;

    /**
     * Original values for global configuration options that need to be restored
     *
     * @var array
     */
    private $_globalConfigValues = array();

    /**
     * Original values for store-scoped configuration options that need to be restored
     *
     * @var array
     */
    private $_storeConfigValues = array();

    /**
     * Constructor stores the first valid listener instance and uses it further if null is passed
     *
     * @param Magento_Test_Listener $listener
     */
    public function __construct($listener = null)
    {
        if (!self::$_listenerDefault) {
            self::$_listenerDefault = $listener;
        }
        $this->_listener = ($listener ? $listener : self::$_listenerDefault);
    }

    /**
     * Retrieve configuration node value
     *
     * @param string $configPath
     * @param string|bool|null $storeCode
     * @return string
     */
    protected function _getConfigValue($configPath, $storeCode = false)
    {
        if ($storeCode === false) {
            $result = Mage::getConfig()->getNode($configPath);
        } else {
            $result = Mage::getStoreConfig($configPath, $storeCode);
        }
        if ($result instanceof SimpleXMLElement) {
            $result = (string)$result;
        }
        return $result;
    }

    /**
     * Assign configuration node value
     *
     * @param string $configPath
     * @param string $value
     * @param string|bool|null $storeCode
     */
    protected function _setConfigValue($configPath, $value, $storeCode = false)
    {
        if ($storeCode === false) {
            Mage::getConfig()->setNode($configPath, $value);
        } else {
            Mage::app()->getStore($storeCode)->setConfig($configPath, $value);
        }
    }

    /**
     * Assign required config values and save original ones
     */
    protected function _assignConfigData()
    {
        if (!$this->_listener || !$this->_listener->getCurrentTest()) {
            return;
        }
        $annotations = $this->_listener->getCurrentTest()->getAnnotations();
        if (!isset($annotations['method']['magentoConfigFixture'])) {
            return;
        }
        foreach ($annotations['method']['magentoConfigFixture'] as $configPathAndValue) {
            if (preg_match('/^.+?(?=_store\s)/', $configPathAndValue, $matches)) {
                /* Store-scoped config value */
                $storeCode = ($matches[0] != 'current' ? $matches[0] : '');
                list(, $configPath, $requiredValue) = preg_split('/\s+/', $configPathAndValue, 3);

                $originalValue = $this->_getConfigValue($configPath, $storeCode);
                $this->_storeConfigValues[$storeCode][$configPath] = $originalValue;

                $this->_setConfigValue($configPath, $requiredValue, $storeCode);
            } else {
                /* Global config value */
                list($configPath, $requiredValue) = preg_split('/\s+/', $configPathAndValue, 2);

                $originalValue = $this->_getConfigValue($configPath);
                $this->_globalConfigValues[$configPath] = $originalValue;

                $this->_setConfigValue($configPath, $requiredValue);
            }

        }
    }

    /**
     * Restore original values for changed config options
     */
    protected function _restoreConfigData()
    {
        /* Restore global values */
        foreach ($this->_globalConfigValues as $configPath => $originalValue) {
            $this->_setConfigValue($configPath, $originalValue);
        }
        $this->_globalConfigValues = array();

        /* Restore store-scoped values */
        foreach ($this->_storeConfigValues as $storeCode => $originalData) {
            foreach ($originalData as $configPath => $originalValue) {
                $this->_setConfigValue($configPath, $originalValue, $storeCode);
            }
        }
        $this->_storeConfigValues = array();
    }

    /**
     * Handler for 'startTest' event
     */
    public function startTest()
    {
        $this->_assignConfigData();
        self::$_isWithinTest = true;
    }

    /**
     * Handler for 'endTest' event
     */
    public function endTest()
    {
        self::$_isWithinTest = false;
        $this->_restoreConfigData();
    }

    /**
     * Handler for 'controller_front_init_before' event
     */
    public function initFrontControllerBefore()
    {
        if (!self::$_isWithinTest) {
            return;
        }
        $this->_assignConfigData();
    }
}
