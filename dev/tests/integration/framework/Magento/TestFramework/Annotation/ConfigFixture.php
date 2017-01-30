<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Implementation of the @magentoConfigFixture DocBlock annotation
 */
namespace Magento\TestFramework\Annotation;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Handler which works with magentoConfigFixture annotations
 *
 * @package Magento\TestFramework\Annotation
 */
class ConfigFixture
{
    /**
     * Test instance that is available between 'startTest' and 'stopTest' events
     *
     * @var \PHPUnit_Framework_TestCase
     */
    protected $_currentTest;

    /**
     * Original values for global configuration options that need to be restored
     *
     * @var array
     */
    private $_globalConfigValues = [];

    /**
     * Original values for store-scoped configuration options that need to be restored
     *
     * @var array
     */
    private $_storeConfigValues = [];

    /**
     * Retrieve configuration node value
     *
     * @param string $configPath
     * @param string|bool|null $scopeCode
     * @return string
     */
    protected function _getConfigValue($configPath, $scopeCode = null)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $result = null;
        if ($scopeCode !== false) {
            /** @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
            $scopeConfig = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
            $result = $scopeConfig->getValue(
                $configPath,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $scopeCode
            );
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
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        if ($storeCode === false) {
            if (strpos($configPath, 'default/') === 0) {
                $configPath = substr($configPath, 8);
                $objectManager->get(
                    'Magento\Framework\App\Config\MutableScopeConfigInterface'
                )->setValue(
                    $configPath,
                    $value,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );
            }
        } else {
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Framework\App\Config\MutableScopeConfigInterface'
            )->setValue(
                $configPath,
                $value,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeCode
            );
        }
    }

    /**
     * Assign required config values and save original ones
     *
     * @param \PHPUnit_Framework_TestCase $test
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _assignConfigData(\PHPUnit_Framework_TestCase $test)
    {
        $annotations = $test->getAnnotations();
        if (!isset($annotations['method']['magentoConfigFixture'])) {
            return;
        }
        foreach ($annotations['method']['magentoConfigFixture'] as $configPathAndValue) {
            if (preg_match('/^.+?(?=_store\s)/', $configPathAndValue, $matches)) {
                /* Store-scoped config value */
                $storeCode = $matches[0] != 'current' ? $matches[0] : null;
                $parts = preg_split('/\s+/', $configPathAndValue, 3);
                list($configScope, $configPath, $requiredValue) = $parts + ['', '', ''];
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
        $this->_globalConfigValues = [];

        /* Restore store-scoped values */
        foreach ($this->_storeConfigValues as $storeCode => $originalData) {
            foreach ($originalData as $configPath => $originalValue) {
                if (empty($storeCode)) {
                    $storeCode = null;
                }
                $this->_setConfigValue($configPath, $originalValue, $storeCode);
            }
        }
        $this->_storeConfigValues = [];
    }

    /**
     * Handler for 'startTest' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     */
    public function startTest(\PHPUnit_Framework_TestCase $test)
    {
        $this->_currentTest = $test;
        $this->_assignConfigData($test);
    }

    /**
     * Handler for 'endTest' event
     *
     * @param \PHPUnit_Framework_TestCase $test
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(\PHPUnit_Framework_TestCase $test)
    {
        $this->_currentTest = null;
        $this->_restoreConfigData();
    }

    /**
     * Reassign configuration data whenever application is reset
     */
    public function initStoreAfter()
    {
        /* process events triggered from within a test only */
        if ($this->_currentTest) {
            $this->_assignConfigData($this->_currentTest);
        }
    }
}
