<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Setup\Patch;

use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SetupInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class Patch209
{


    /**
     * @param CustomerSetupFactory $customerSetupFactory @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory,
                                \Magento\Eav\Model\Config $eavConfig)
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $setup->getConnection()->beginTransaction();

        try {
            $this->migrateStoresAllowedCountriesToWebsite($setup);
            $setup->getConnection()->commit();
        } catch (\Exception $e) {
            $setup->getConnection()->rollBack();
            throw $e;
        }


        $this->eavConfig->clear();
        $setup->endSetup();

    }

    private function migrateStoresAllowedCountriesToWebsite(SetupInterface $setup
    )
    {
        $allowedCountries = [];
        //Process Websites
        foreach ($this->getStoreManager()->getStores() as $store) {
            $allowedCountries = $this->mergeAllowedCountries(
                $allowedCountries,
                $this->getAllowedCountries(ScopeInterface::SCOPE_STORE, $store->getId()),
                $store->getWebsiteId()
            );
        }
        //Process stores
        foreach ($this->getStoreManager()->getWebsites() as $website) {
            $allowedCountries = $this->mergeAllowedCountries(
                $allowedCountries,
                $this->getAllowedCountries(ScopeInterface::SCOPE_WEBSITE, $website->getId()),
                $website->getId()
            );
        }

        $connection = $setup->getConnection();

        //Remove everything from stores scope
        $connection->delete(
            $setup->getTable('core_config_data'),
            [
                'path = ?' => AllowedCountries::ALLOWED_COUNTRIES_PATH,
                'scope = ?' => ScopeInterface::SCOPE_STORES
            ]
        );

        //Update websites
        foreach ($allowedCountries as $scopeId => $countries) {
            $connection->update(
                $setup->getTable('core_config_data'),
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

    private function getStoreManager()
    {
        if (!$this->storeManager) {
            $this->storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        }

        return $this->storeManager;

    }

    private function mergeAllowedCountries(array $countries, array $newCountries, $identifier
    )
    {
        if (!isset($countries[$identifier])) {
            $countries[$identifier] = $newCountries;
        } else {
            $countries[$identifier] =
                array_replace($countries[$identifier], $newCountries);
        }

        return $countries;

    }

    private function getAllowedCountries($scope, $scopeCode
    )
    {
        $reader = $this->getAllowedCountriesReader();
        return $reader->makeCountriesUnique($reader->getCountriesFromConfig($scope, $scopeCode));

    }

    private function getAllowedCountriesReader()
    {
        if (!$this->allowedCountriesReader) {
            $this->allowedCountriesReader = ObjectManager::getInstance()->get(AllowedCountries::class);
        }

        return $this->allowedCountriesReader;

    }
}
