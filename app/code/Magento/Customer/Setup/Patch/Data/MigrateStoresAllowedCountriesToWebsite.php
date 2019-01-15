<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch\Data;

use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Directory\Model\AllowedCountriesFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class MigrateStoresAllowedCountriesToWebsite implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AllowedCountriesFactory
     */
    private $allowedCountries;

    /**
     * MigrateStoresAllowedCountriesToWebsite constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StoreManagerInterface $storeManager
     * @param AllowedCountries $allowedCountries
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\AllowedCountries $allowedCountries
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->storeManager = $storeManager;
        $this->allowedCountries = $allowedCountries;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->beginTransaction();

        try {
            $this->migrateStoresAllowedCountriesToWebsite();
            $this->moduleDataSetup->getConnection()->commit();
        } catch (\Exception $e) {
            $this->moduleDataSetup->getConnection()->rollBack();
            throw $e;
        }
    }

    /**
     * Merge allowed countries from stores to websites
     *
     * @return void
     */
    private function migrateStoresAllowedCountriesToWebsite()
    {
        $allowedCountries = [];
        //Process Websites
        foreach ($this->storeManager->getStores() as $store) {
            $allowedCountries = $this->mergeAllowedCountries(
                $allowedCountries,
                $this->getAllowedCountries(ScopeInterface::SCOPE_STORE, $store->getId()),
                $store->getWebsiteId()
            );
        }
        //Process stores
        foreach ($this->storeManager->getWebsites() as $website) {
            $allowedCountries = $this->mergeAllowedCountries(
                $allowedCountries,
                $this->getAllowedCountries(ScopeInterface::SCOPE_WEBSITE, $website->getId()),
                $website->getId()
            );
        }

        $connection = $this->moduleDataSetup->getConnection();

        //Remove everything from stores scope
        $connection->delete(
            $this->moduleDataSetup->getTable('core_config_data'),
            [
                'path = ?' => AllowedCountries::ALLOWED_COUNTRIES_PATH,
                'scope = ?' => ScopeInterface::SCOPE_STORES
            ]
        );

        //Update websites
        foreach ($allowedCountries as $scopeId => $countries) {
            $connection->update(
                $this->moduleDataSetup->getTable('core_config_data'),
                [
                    'value' => implode(',', $countries)
                ],
                [
                    'path = ?' => AllowedCountries::ALLOWED_COUNTRIES_PATH,
                    'scope_id = ?' => $scopeId,
                    'scope = ?' => ScopeInterface::SCOPE_WEBSITES
                ]
            );
        }
    }

    /**
     * Retrieve countries not depending on global scope
     *
     * @param string $scope
     * @param int $scopeCode
     * @return array
     */
    private function getAllowedCountries($scope, $scopeCode)
    {
        return $this->allowedCountries->makeCountriesUnique(
            $this->allowedCountries->getCountriesFromConfig($scope, $scopeCode)
        );
    }

    /**
     * Merge allowed countries between different scopes
     *
     * @param array $countries
     * @param array $newCountries
     * @param string $identifier
     * @return array
     */
    private function mergeAllowedCountries(array $countries, array $newCountries, $identifier)
    {
        if (!isset($countries[$identifier])) {
            $countries[$identifier] = $newCountries;
        } else {
            $countries[$identifier] = array_replace($countries[$identifier], $newCountries);
        }

        return $countries;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            UpdateAutocompleteOnStorefrontConfigPath::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.9';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
