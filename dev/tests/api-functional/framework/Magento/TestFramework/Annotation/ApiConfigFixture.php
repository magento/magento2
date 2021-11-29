<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\App\ApiMutableScopeConfig;
use Magento\TestFramework\Config\Model\ConfigStorage;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @inheritDoc
 */
class ApiConfigFixture extends ConfigFixture
{
    /**
     * Values need to be deleted form the database
     *
     * @var array
     */
    private $valuesToDeleteFromDatabase = [];

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setStoreConfigValue(array $matches, $configPathAndValue): void
    {
        $storeCode = $matches[0];
        $parts = preg_split('/\s+/', $configPathAndValue, 3);
        [$configScope, $configPath, $requiredValue] = $parts + ['', '', ''];
        /** @var ConfigStorage $configStorage */
        $configStorage = Bootstrap::getObjectManager()->get(ConfigStorage::class);
        if (!$configStorage->checkIsRecordExist($configPath, ScopeInterface::SCOPE_STORES, $storeCode)) {
            $this->valuesToDeleteFromDatabase[$storeCode][$configPath ?? ''] = $requiredValue ?? '';
        }

        parent::setStoreConfigValue($matches, $configPathAndValue);
    }

    /**
     * @inheritdoc
     */
    protected function setGlobalConfigValue($configPathAndValue): void
    {
        [$configPath, $requiredValue] = preg_split('/\s+/', $configPathAndValue, 2);
        /** @var ConfigStorage $configStorage */
        $configStorage = Bootstrap::getObjectManager()->get(ConfigStorage::class);
        if (!$configStorage->checkIsRecordExist($configPath)) {
            $this->valuesToDeleteFromDatabase['global'][$configPath] = $requiredValue;
        }

        $originalValue = $this->getScopeConfigValue($configPath, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $this->globalConfigValues[$configPath] = $originalValue;
        $this->_setConfigValue($configPath, $requiredValue);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function setWebsiteConfigValue(array $matches, $configPathAndValue): void
    {
        $websiteCode = $matches[0];
        $parts = preg_split('/\s+/', $configPathAndValue, 3);
        [$configScope, $configPath, $requiredValue] = $parts + ['', '', ''];
        /** @var ConfigStorage $configStorage */
        $configStorage = Bootstrap::getObjectManager()->get(ConfigStorage::class);
        if (!$configStorage->checkIsRecordExist($configPath, ScopeInterface::SCOPE_WEBSITES, $websiteCode)) {
            $this->valuesToDeleteFromDatabase[$websiteCode][$configPath ?? ''] = $requiredValue ?? '';
        }

        parent::setWebsiteConfigValue($matches, $configPathAndValue);
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _restoreConfigData()
    {
        /** @var ConfigResource $configResource */
        $configResource = Bootstrap::getObjectManager()->get(ConfigResource::class);
        /* Restore global values */
        foreach ($this->globalConfigValues as $configPath => $originalValue) {
            if (isset($this->valuesToDeleteFromDatabase['global'][$configPath])) {
                $configResource->deleteConfig($configPath);
            } else {
                $this->_setConfigValue($configPath, $originalValue);
            }
        }
        $this->globalConfigValues = [];
        /* Restore store-scoped values */
        foreach ($this->storeConfigValues as $storeCode => $originalData) {
            foreach ($originalData as $configPath => $originalValue) {
                $storeCode = $storeCode ?: null;
                if (isset($this->valuesToDeleteFromDatabase[$storeCode][$configPath])) {
                    $scopeId = $this->getIdByScopeType(ScopeInterface::SCOPE_STORES, $storeCode);
                    $configResource->deleteConfig($configPath, ScopeInterface::SCOPE_STORES, $scopeId);
                } else {
                    $this->setScopeConfigValue(
                        $configPath,
                        (string)$originalValue,
                        ScopeInterface::SCOPE_STORES,
                        $storeCode
                    );
                }
            }
        }
        $this->storeConfigValues = [];
        /* Restore website-scoped values */
        foreach ($this->websiteConfigValues as $websiteCode => $originalData) {
            foreach ($originalData as $configPath => $originalValue) {
                $websiteCode = $websiteCode ?: null;
                if (isset($this->valuesToDeleteFromDatabase[$websiteCode][$configPath])) {
                    $scopeId = $this->getIdByScopeType(ScopeInterface::SCOPE_WEBSITES, $websiteCode);
                    $configResource->deleteConfig($configPath, ScopeInterface::SCOPE_WEBSITES, $scopeId);
                } else {
                    $this->setScopeConfigValue(
                        $configPath,
                        $originalValue,
                        ScopeInterface::SCOPE_WEBSITES,
                        $websiteCode
                    );
                }
            }
        }
        $this->websiteConfigValues = [];
    }

    /**
     * @inheritdoc
     */
    protected function getMutableScopeConfig(): MutableScopeConfigInterface
    {
        return Bootstrap::getObjectManager()
            ->get(ApiMutableScopeConfig::class);
    }

    /**
     * @inheritdoc
     */
    protected function getScopeConfigValue(string $configPath, string $scopeType, string $scopeCode = null): ?string
    {
        /** @var ConfigStorage $configStorage */
        $configStorage = Bootstrap::getObjectManager()->get(ConfigStorage::class);
        $result = $configStorage->getValueFromDb($configPath, $scopeType, $scopeCode);

        return $result ?: null;
    }

    /**
     * Get id by code
     *
     * @param string $scopeType
     * @param string|null $scopeId
     * @return int
     */
    private function getIdByScopeType(string $scopeType, ?string $scopeId): int
    {
        $id = 0;
        /** @var StoreManagerInterface $storeManager */
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        switch ($scopeType) {
            case ScopeInterface::SCOPE_WEBSITES:
                $id = (int)$storeManager->getWebsite($scopeId)->getId();
                break;
            case ScopeInterface::SCOPE_STORES:
                $id = (int)$storeManager->getStore($scopeId)->getId();
                break;
            default:
                break;
        }

        return $id;
    }
}
