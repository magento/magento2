<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\App\ApiMutableScopeConfig;
use Magento\TestFramework\Config\Model\ConfigStorage;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @inheritDoc
 */
class ApiConfigFixture extends ConfigFixture
{
    /**
     * Values are inherited
     *
     * @var array
     */
    private $valuesNotFromDatabase = [];

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
            $this->valuesNotFromDatabase[$storeCode][$configPath ?? ''] = $requiredValue ?? '';
        }

        parent::setStoreConfigValue($matches, $configPathAndValue);
    }

    /**
     * @inheritdoc
     */
    protected function setGlobalConfigValue($configPathAndValue): void
    {
        [$configPath, $requiredValue] = preg_split('/\s+/', $configPathAndValue, 2);
        $configPath = str_starts_with($configPath, 'default/') ? substr($configPath, 8) : $configPath;
        /** @var ConfigStorage $configStorage */
        $configStorage = Bootstrap::getObjectManager()->get(ConfigStorage::class);
        if (!$configStorage->checkIsRecordExist($configPath)) {
            $this->valuesNotFromDatabase['global'][$configPath] = $requiredValue;
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
            $this->valuesNotFromDatabase[$websiteCode][$configPath ?? ''] = $requiredValue ?? '';
        }

        parent::setWebsiteConfigValue($matches, $configPathAndValue);
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _restoreConfigData()
    {
        /* Restore global values */
        foreach ($this->globalConfigValues as $configPath => $originalValue) {
            if (isset($this->valuesNotFromDatabase['global'][$configPath])) {
                $this->inheritConfig(
                    $configPath,
                    $originalValue,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                );
            } else {
                $this->_setConfigValue($configPath, $originalValue);
            }
        }
        $this->globalConfigValues = [];
        /* Restore store-scoped values */
        foreach ($this->storeConfigValues as $storeCode => $originalData) {
            foreach ($originalData as $configPath => $originalValue) {
                $storeCode = $storeCode ?: null;
                if (isset($this->valuesNotFromDatabase[$storeCode][$configPath])) {
                    $this->inheritConfig(
                        $configPath,
                        (string)$originalValue,
                        ScopeInterface::SCOPE_STORES,
                        $storeCode
                    );
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
                if (isset($this->valuesNotFromDatabase[$websiteCode][$configPath])) {
                    $this->inheritConfig(
                        $configPath,
                        $originalValue,
                        ScopeInterface::SCOPE_WEBSITES,
                        $websiteCode
                    );
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
     * Inherit the config and remove the config from database
     *
     * @param string $path
     * @param string $value
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return void
     */
    private function inheritConfig(
        string $path,
        ?string $value,
        string $scopeType,
        ?string $scopeCode
    ) {
        $pathParts = explode('/', $path);
        $store = 0;
        $configData = [
            'section' => $pathParts[0],
            'website' => '',
            'store' => $store,
            'groups' => [
                $pathParts[1] => [
                    'fields' => [
                        $pathParts[2] => [
                            'value' => $value,
                            'inherit' => 1
                        ]
                    ]
                ]
            ]
        ];
        $objectManager = Bootstrap::getObjectManager();
        if ($scopeType === ScopeInterface::SCOPE_STORE && $scopeCode !== null) {
            $store = $objectManager->get(StoreRepositoryInterface::class)->get($scopeCode)->getId();
            $configData['store'] = $store;
        } elseif ($scopeType === ScopeInterface::SCOPE_WEBSITES && $scopeCode !== null) {
            $website = $objectManager->get(WebsiteRepositoryInterface::class)->get($scopeCode)->getId();
            $configData['store'] = '';
            $configData['website'] = $website;
        }

        $objectManager->get(ConfigFactory::class)->create(['data' => $configData])->save();
    }
}
