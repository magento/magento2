<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\Config\Model\Config;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * @inheritDoc
 */
class ApiConfigFixture extends ConfigFixture
{
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
     * Values need to be deleted form the database
     *
     * @var array
     */
    private $_valuesToDeleteFromDatabase = [];

    /**
     * Assign required config values and save original ones
     *
     * @param TestCase $test
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _assignConfigData(TestCase $test)
    {
        $annotations = $test->getAnnotations();
        if (!isset($annotations['method'][$this->annotation])) {
            return;
        }
        foreach ($annotations['method'][$this->annotation] as $configPathAndValue) {
            if (preg_match('/^.+?(?=_store\s)/', $configPathAndValue, $matches)) {
                /* Store-scoped config value */
                $storeCode = $matches[0];
                $parts = preg_split('/\s+/', $configPathAndValue, 3);
                list($configScope, $configPath, $requiredValue) = $parts + ['', '', ''];
                $originalValue = $this->_getConfigValue($configPath, $storeCode);
                $this->_storeConfigValues[$storeCode][$configPath] = $originalValue;
                if ($this->checkIfValueExist($configPath, $storeCode)) {
                    $this->_valuesToDeleteFromDatabase[$storeCode][$configPath] = $requiredValue;
                }
                $this->_setConfigValue($configPath, $requiredValue, $storeCode);
            } else {
                /* Global config value */
                list($configPath, $requiredValue) = preg_split('/\s+/', $configPathAndValue, 2);

                $originalValue = $this->_getConfigValue($configPath);
                $this->_globalConfigValues[$configPath] = $originalValue;
                if ($this->checkIfValueExist($configPath)) {
                    $this->_valuesToDeleteFromDatabase['global'][$configPath] = $requiredValue;
                }

                $this->_setConfigValue($configPath, $requiredValue);
            }
        }
    }

    /**
     * Restore original values for changed config options
     */
    protected function _restoreConfigData()
    {
        $configResource = Bootstrap::getObjectManager()->get(ConfigResource::class);

        /* Restore global values */
        foreach ($this->_globalConfigValues as $configPath => $originalValue) {
            if (isset($this->_valuesToDeleteFromDatabase['global'][$configPath])) {
                $configResource->deleteConfig($configPath);
            } else {
                $this->_setConfigValue($configPath, $originalValue);
            }
        }
        $this->_globalConfigValues = [];

        /* Restore store-scoped values */
        foreach ($this->_storeConfigValues as $storeCode => $originalData) {
            foreach ($originalData as $configPath => $originalValue) {
                if (empty($storeCode)) {
                    $storeCode = null;
                }
                if (isset($this->_valuesToDeleteFromDatabase[$storeCode][$configPath])) {
                    $scopeId = $this->getStoreIdByCode($storeCode);
                    $configResource->deleteConfig($configPath, 'stores', $scopeId);
                } else {
                    $this->_setConfigValue($configPath, $originalValue, $storeCode);
                }
            }
        }
        $this->_storeConfigValues = [];
    }

    /**
     * Load configs by path and scope
     *
     * @param string $configPath
     * @param string $storeCode
     * @return Config[]
     */
    private function loadConfigs(string $configPath, string $storeCode = null): array
    {
        $configCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
        $collection = $configCollectionFactory->create();
        $scope = $storeCode ? 'stores' : 'default';
        $scopeId = $storeCode ? $this->getStoreIdByCode($storeCode) : 0;

        $collection->addScopeFilter($scope, $scopeId, $configPath);
        return $collection->getItems();
    }

    /**
     * Check if config exist in the database
     *
     * @param string        $configPath
     * @param string|null   $storeCode
     */
    private function checkIfValueExist(string $configPath, string $storeCode = null): bool
    {
        $configs = $this->loadConfigs($configPath, $storeCode);

        return !(bool)$configs;
    }

    /**
     * Returns the store ID by the store code
     *
     * @param  string $storeCode
     * @return int
     */
    private function getStoreIdByCode(string $storeCode): int
    {
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $store = $storeManager->getStore($storeCode);
        return (int)$store->getId();
    }

    /**
     * @inheritDoc
     */
    protected function _setConfigValue($configPath, $value, $storeCode = false)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        if ($storeCode === false) {
            $objectManager->get(
                \Magento\TestFramework\App\ApiMutableScopeConfig::class
            )->setValue(
                $configPath,
                $value,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );

            return;
        }
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\TestFramework\App\ApiMutableScopeConfig::class
        )->setValue(
            $configPath,
            $value,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
}
