<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Implementation of the @magentoAdminConfigFixture DocBlock annotation
 */
namespace Magento\TestFramework\Annotation;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\TestFramework\Annotation\TestCaseAnnotation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use PHPUnit\Framework\TestCase;

/**
 * Handler for applying magentoAdminConfigFixture annotation
 */
class AdminConfigFixture
{
    public const ANNOTATION = 'magentoAdminConfigFixture';

    /**
     * The test instance that is available between 'startTest' and 'stopTest' events.
     *
     * @var TestCase
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
        return Bootstrap::getObjectManager()->get(MutableScopeConfigInterface::class)->getValue($configPath);
    }

    /**
     * Assign configuration node value
     *
     * @param string $configPath
     * @param string $value
     * @return void
     */
    protected function _setConfigValue($configPath, $value)
    {
        Bootstrap::getObjectManager()->get(MutableScopeConfigInterface::class)->setValue($configPath, $value);
    }

    /**
     * Assign required config values and save original ones
     *
     * @param TestCase $test
     * @return void
     */
    protected function _assignConfigData(TestCase $test)
    {
        $resolver = Resolver::getInstance();
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        $existingFixtures = $annotations['method'][self::ANNOTATION] ?? [];
        /* Need to be applied even test does not have added fixtures because fixture can be added via config */
        $testAnnotations = $resolver->applyConfigFixtures($test, $existingFixtures, self::ANNOTATION);
        foreach ($testAnnotations as $configPathAndValue) {
            list($configPath, $requiredValue) = preg_split('/\s+/', $configPathAndValue, 2);

            $originalValue = $this->_getConfigValue($configPath);
            $this->_configValues[$configPath] = $originalValue;

            $this->_setConfigValue($configPath, $requiredValue);
        }
    }

    /**
     * Restore original values for changed config options
     *
     * @return void
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
     * @param TestCase $test
     * @return void
     */
    public function startTest(TestCase $test)
    {
        $this->_currentTest = $test;
        $this->_assignConfigData($test);
    }

    /**
     * Handler for 'endTest' event
     *
     * @param TestCase $test
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function endTest(TestCase $test)
    {
        $this->_currentTest = null;
        $this->_restoreConfigData();
    }

    /**
     * Reassign configuration data whenever application is reset
     *
     * @return void
     */
    public function initStoreAfter()
    {
        /* process events triggered from within a test only */
        if ($this->_currentTest) {
            $this->_assignConfigData($this->_currentTest);
        }
    }
}
