<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Config\Model;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class checks config table data using direct calls
 */
class ConfigStorage
{
    /** @var ConfigInterface */
    private $configResource;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param ConfigInterface $configResource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ConfigInterface $configResource, StoreManagerInterface $storeManager)
    {
        $this->configResource = $configResource;
        $this->storeManager = $storeManager;
    }

    /**
     * Get value from db
     *
     * @param string $path
     * @param string $scope
     * @param string|null $scopeCode
     * @return string|false
     */
    public function getValueFromDb(
        string $path,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        ?string $scopeCode = null
    ) {
        $connect = $this->configResource->getConnection();
        $scope = $this->normalizeScope($scope);
        $scopeId = $this->getIdByScope($scope, $scopeCode);

        $select = $connect->select()->from(['main_table' => $this->configResource->getMainTable()], 'value')
            ->where('main_table.path = ?', $path)
            ->where('main_table.scope = ?', $scope)
            ->where('main_table.scope_id = ?', $scopeId);

        return $connect->fetchOne($select);
    }

    /**
     * Check is record exist in DB
     *
     * @param string $path
     * @param string $scope
     * @param string|null $scopeCode
     * @return bool
     */
    public function checkIsRecordExist(
        string $path,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        ?string $scopeCode = null
    ): bool {
        $connect = $this->configResource->getConnection();
        $scope = $this->normalizeScope($scope);
        $scopeId = $this->getIdByScope($scope, $scopeCode);

        $select = $connect->select()->from(['main_table' => $this->configResource->getMainTable()], 'COUNT(*)')
            ->where('main_table.path = ?', $path)
            ->where('main_table.scope = ?', $scope)
            ->where('main_table.scope_id = ?', $scopeId);

        return (bool)$connect->fetchOne($select);
    }

    /**
     * Get scope id by scope code
     *
     * @param string $scope
     * @param string|null $scopeCode
     * @return int
     */
    private function getIdByScope(string $scope, ?string $scopeCode): int
    {
        $scopeId = 0;

        if ($scope === ScopeInterface::SCOPE_WEBSITES) {
            $scopeId = (int)$this->storeManager->getWebsite($scopeCode)->getId();
        } elseif ($scope === ScopeInterface::SCOPE_STORES) {
            $scopeId = (int)$this->storeManager->getStore($scopeCode)->getId();
        }

        return $scopeId;
    }

    /**
     * Normalize scope
     *
     * @param string $scope
     * @return string
     */
    private function normalizeScope(string $scope): string
    {
        if ($scope === ScopeInterface::SCOPE_WEBSITE) {
            $scope = ScopeInterface::SCOPE_WEBSITES;
        }
        if ($scope === ScopeInterface::SCOPE_STORE) {
            $scope = ScopeInterface::SCOPE_STORES;
        }

        return $scope;
    }
}
