<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Implementation of the @magentoConfigFixture DocBlock annotation
 */
namespace Magento\TestFramework\Annotation;

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Annotation\TestCaseAnnotation;
use Magento\TestFramework\Event\Magento;
use Magento\TestFramework\Fixture\Parser\Config as ConfigFixtureParser;
use Magento\TestFramework\Fixture\ParserInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use PHPUnit\Framework\TestCase;

/**
 * Handler which works with magentoConfigFixture annotations
 */
class ConfigFixture
{
    public const ANNOTATION = 'magentoConfigFixture';

    /**
     * Test instance that is available between 'startTest' and 'stopTest' events
     *
     * @var TestCase
     */
    protected $_currentTest;

    /**
     * Original values for global configuration options that need to be restored
     *
     * @var array
     */
    protected $globalConfigValues = [];

    /**
     * Original values for website-scoped configuration options that need to be restored
     *
     * @var array
     */
    protected $websiteConfigValues = [];

    /**
     * Original values for store-scoped configuration options that need to be restored
     *
     * @var array
     */
    protected $storeConfigValues = [];

    /**
     * Retrieve configuration node value
     *
     * @param string $configPath
     * @param string|bool|null $scopeCode
     * @return string
     */
    protected function _getConfigValue($configPath, $scopeCode = null)
    {
        return $this->getScopeConfigValue($configPath, ScopeInterface::SCOPE_STORE, $scopeCode);
    }

    /**
     * Retrieve scope configuration node value
     *
     * @param string $configPath
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return mixed|null
     */
    protected function getScopeConfigValue(string $configPath, string $scopeType, ?string $scopeCode = null)
    {
        $result = null;
        if ($scopeCode !== false) {
            $scopeConfig = $this->getScopeConfig();
            $result = $scopeConfig->getValue($configPath, $scopeType, $scopeCode);
        }
        return $result;
    }

    /**
     * Assign configuration node value
     *
     * @param string $configPath
     * @param string $value
     * @param string|bool|null $storeCode
     * @return void
     */
    protected function _setConfigValue($configPath, $value, $storeCode = false)
    {
        $scopeType = $storeCode === false ? ScopeConfigInterface::SCOPE_TYPE_DEFAULT : ScopeInterface::SCOPE_STORE;
        $this->setScopeConfigValue($configPath, $value, $scopeType, $storeCode);
    }

    /**
     * Set config scope value
     *
     * @param string $configPath
     * @param string|null $value
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return void
     */
    protected function setScopeConfigValue(
        string $configPath,
        ?string $value,
        string $scopeType,
        ?string $scopeCode
    ): void {
        $config = $this->getMutableScopeConfig();
        if (strpos($configPath, 'default/') === 0) {
            $configPath = substr($configPath, 8);
            $config->setValue($configPath, $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        } else {
            $config->setValue($configPath, $value, $scopeType, $scopeCode);
        }
    }

    /**
     * Get mutable config object
     *
     * @return MutableScopeConfigInterface
     */
    protected function getMutableScopeConfig(): MutableScopeConfigInterface
    {
        return Bootstrap::getObjectManager()->get(MutableScopeConfigInterface::class);
    }

    /**
     * Get config object
     *
     * @return ScopeConfigInterface
     */
    protected function getScopeConfig(): ScopeConfigInterface
    {
        return Bootstrap::getObjectManager()->get(ScopeConfigInterface::class);
    }

    /**
     * Assign required config values and save original ones
     *
     * @param TestCase $test
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _assignConfigData(TestCase $test)
    {
        $resolver = Resolver::getInstance();
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        $existingFixtures = array_merge(
            $annotations['method'][self::ANNOTATION] ?? [],
            $this->getConfigFixturesFromAttribute($test)
        );
        /* Need to be applied even test does not have added fixtures because fixture can be added via config */
        $testAnnotations = $resolver->applyConfigFixtures(
            $test,
            $existingFixtures,
            self::ANNOTATION
        );
        foreach ($testAnnotations as $configPathAndValue) {
            if (preg_match('/^[^\/]+?(?=_store\s)/', $configPathAndValue, $matches)) {
                $this->setStoreConfigValue($matches ?? [], $configPathAndValue);
            } elseif (preg_match('/^[^\/]+?(?=_website\s)/', $configPathAndValue, $matches)) {
                $this->setWebsiteConfigValue($matches ?? [], $configPathAndValue);
            } else {
                $this->setGlobalConfigValue($configPathAndValue);
            }
        }
    }

    /**
     * Sets store-scoped config value
     *
     * @param array $matches
     * @param string $configPathAndValue
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setStoreConfigValue(array $matches, $configPathAndValue): void
    {
        $storeCode = $matches[0] != 'current' ? $matches[0] : null;
        $parts = preg_split('/\s+/', $configPathAndValue, 3);
        list($configScope, $configPath, $requiredValue) = $parts + ['', '', ''];
        $originalValue = $this->_getConfigValue($configPath, $storeCode);
        $this->storeConfigValues[$storeCode][$configPath] = $originalValue;
        $this->_setConfigValue($configPath, $requiredValue, $storeCode);
    }

    /**
     * Sets website-scoped config value
     *
     * @param array $matches
     * @param string $configPathAndValue
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setWebsiteConfigValue(array $matches, $configPathAndValue): void
    {
        $websiteCode = $matches[0] != 'current' ? $matches[0] : null;
        $parts = preg_split('/\s+/', $configPathAndValue, 3);
        list($configScope, $configPath, $requiredValue) = $parts + ['', '', ''];
        $originalValue = $this->getScopeConfigValue($configPath, ScopeInterface::SCOPE_WEBSITES, $websiteCode);
        $this->websiteConfigValues[$websiteCode][$configPath] = $originalValue;
        $this->setScopeConfigValue($configPath, $requiredValue, ScopeInterface::SCOPE_WEBSITES, $websiteCode);
    }

    /**
     * Sets global config value
     *
     * @param string $configPathAndValue
     * @return void
     */
    protected function setGlobalConfigValue($configPathAndValue): void
    {
        /* Global config value */
        list($configPath, $requiredValue) = preg_split('/\s+/', $configPathAndValue, 2);
        $configPath = str_starts_with($configPath, 'default/') ? substr($configPath, 8) : $configPath;
        $originalValue = $this->_getConfigValue($configPath);
        $this->globalConfigValues[$configPath] = $originalValue;
        $this->_setConfigValue($configPath, $requiredValue);
    }

    /**
     * Restore original values for changed config options
     *
     * @return void
     */
    protected function _restoreConfigData()
    {
        /* Restore global values */
        foreach ($this->globalConfigValues as $configPath => $originalValue) {
            $this->_setConfigValue($configPath, $originalValue);
        }
        $this->globalConfigValues = [];

        /* Restore store-scoped values */
        foreach ($this->storeConfigValues as $storeCode => $originalData) {
            foreach ($originalData as $configPath => $originalValue) {
                if (empty($storeCode)) {
                    $storeCode = null;
                }
                $this->setScopeConfigValue($configPath, $originalValue, ScopeInterface::SCOPE_STORES, $storeCode);
            }
        }
        $this->storeConfigValues = [];

        /* Restore website-scoped values */
        foreach ($this->websiteConfigValues as $websiteCode => $originalData) {
            foreach ($originalData as $configPath => $originalValue) {
                if (empty($websiteCode)) {
                    $websiteCode = null;
                }
                $this->setScopeConfigValue($configPath, $originalValue, ScopeInterface::SCOPE_WEBSITES, $websiteCode);
            }
        }
        $this->websiteConfigValues = [];
    }

    /**
     * Handler for 'startTest' event
     *
     * @param TestCase $test
     * @return void
     */
    public function startTest(TestCase $test)
    {
        if ($eventObj = Magento::getCurrentEventObject()) {
            $testData = $eventObj->test()->testData();

            if ($testData->hasDataFromDataProvider()) {
                $dataFromDataProvider = $testData->dataFromDataProvider();
                $dataSetName = $dataFromDataProvider->dataSetName();
                $test->setData($dataSetName, ['']);
            }
        }

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

    /**
     * Returns config fixtures defined using Config attribute
     *
     * @param TestCase $test
     * @return array
     * @throws LocalizedException
     */
    private function getConfigFixturesFromAttribute(TestCase $test): array
    {
        $parser = Bootstrap::getObjectManager()->get(ConfigFixtureParser::class);
        $configs = [];

        foreach ($parser->parse($test, ParserInterface::SCOPE_METHOD) as $data) {
            $configs[] = sprintf(
                '%s%s %s',
                $data['scopeType'] === ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                    ? 'default/'
                    : ($data['scopeValue'] ?? 'current') . '_' . $data['scopeType'] . ' ',
                $data['path'],
                $data['value'],
            );
        }

        return $configs;
    }
}
