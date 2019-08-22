<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Implementation of the @magentoAdminConfigFixture DocBlock annotation
 */
namespace Magento\TestFramework\Annotation;

/**
 * Handler for applying magentoAdminConfig annotation
 *
 * @package Magento\TestFramework\Annotation
 */
class AdminConfigFixture
{
    /**
     * Test instance that is available between 'startTest' and 'stopTest' events
     *
     * @var \PHPUnit\Framework\TestCase
     */
    protected $_currentTest;

    /**
     * Original values for global configuration options that need to be restored
     *
     * @var array
     */
    private $_configValues = [];

    /**
     * Retrieve configuration node value
     *
     * @param string $configPath
     * @return string
     */
    protected function _getConfigValue($configPath)
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\Config\MutableScopeConfigInterface::class
        )->getValue(
            $configPath
        );
    }

    /**
     * Assign configuration node value
     *
     * @param string $configPath
     * @param string $value
     */
    protected function _setConfigValue($configPath, $value)
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\Config\MutableScopeConfigInterface::class
        )->setValue(
            $configPath,
            $value
        );
    }

    /**
     * Assign required config values and save original ones
     *
     * @param \PHPUnit\Framework\TestCase $test
     */
    protected function _assignConfigData(\PHPUnit\Framework\TestCase $test)
    {
        $annotations = $test->getAnnotations();
        if (!isset($annotations['method']['magentoAdminConfigFixture'])) {
            return;
        }
        foreach ($annotations['method']['magentoAdminConfigFixture'] as $configPathAndValue) {
            list($configPath, $requiredValue) = preg_split('/\s+/', $configPathAndValue, 2);

            $originalValue = $this->_getConfigValue($configPath);
            $this->_configValues[$configPath] = $originalValue;

            $this->_setConfigValue($configPath, $requiredValue);
        }
    }

    /**
     * Restore original values for changed config options
     */
    protected function _restoreConfigData()
    {
        foreach ($this->_configValues as $configPath => $originalValue) {
            $this->_setConfigValue($configPath, $originalValue);
        }
        $this->_configValues = [];
    }

    /**
     * Handler for 'startTest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        $this->_currentTest = $test;
        $this->_assignConfigData($test);
    }

    /**
     * Handler for 'endTest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(\PHPUnit\Framework\TestCase $test)
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
