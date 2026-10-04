<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Directory\Setup;

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\AppInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Add Required Regions for Country
 */
class DataInstaller
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * DatInstaller constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param RegionCollectionFactory $regionCollectionFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        RegionCollectionFactory $regionCollectionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * Add country-region data.
     *
     * @param  AdapterInterface $adapter
     * @param  array $data
     * @return void
     */
    public function addCountryRegions(AdapterInterface $adapter, array $data): void
    {
        /**
         * Fill table directory/country_region
         * Fill table directory/country_region_name for en_US locale
         *
         * state_required config is set initially by InitializeDirectoryData and can be
         * changed in admin or via a dedicated data patch if a new country must require region.
         */
        foreach ($data as $row) {
            $bind = ['country_id' => $row[0], 'code' => $row[1], 'default_name' => $row[2]];
            $adapter->insert($this->resourceConnection->getTableName('directory_country_region'), $bind);
            $regionId = $adapter->lastInsertId($this->resourceConnection->getTableName('directory_country_region'));
            $bind = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $row[2]];
            $adapter->insert($this->resourceConnection->getTableName('directory_country_region_name'), $bind);
        }
    }

    /**
     * Update country-region codes and optionally names.
     *
     * @param AdapterInterface $adapter
     * @param string $countryCode
     * @param array $codeMapping Array of ['old_code' => 'new_code'] mappings
     * @param array $nameMapping Array of ['old_code' => 'new_name'] mappings (optional)
     * @return void
     */
    public function updateCountryRegionCodes(
        AdapterInterface $adapter,
        string $countryCode,
        array $codeMapping,
        array $nameMapping = []
    ): void {
        if (empty($codeMapping)) {
            return;
        }

        $regionCollection = $this->regionCollectionFactory->create();
        $regionCollection->addCountryFilter($countryCode);
        $regionCollection->addRegionCodeFilter(array_keys($codeMapping));

        $regionItems = $regionCollection->getItems();
        if (empty($regionItems)) {
            return;
        }

        $countryRegionDataToUpdate = [];
        $countryRegionNameDataToUpdate = [];

        foreach ($regionItems as $regionItem) {
            $oldCode = $regionItem->getData('code');
            $newCode = $codeMapping[$oldCode] ?? null;

            if ($newCode === null) {
                continue;
            }

            $newName = $nameMapping[$oldCode] ?? $regionItem->getData('default_name');

            // Collect data to update in the 'directory_country_region' table
            $countryRegionDataToUpdate[] = [
                'region_id' => $regionItem->getData('region_id'),
                'country_id' => $regionItem->getData('country_id'),
                'code' => $newCode,
                'default_name' => $newName,
            ];

            // Collect data to update in the 'directory_country_region_name' table
            $countryRegionNameDataToUpdate[] = [
                'locale' => AppInterface::DISTRO_LOCALE_CODE,
                'region_id' => $regionItem->getData('region_id'),
                'name' => $newName
            ];
        }

        // Update region tables with new region codes and names
        if (!empty($countryRegionDataToUpdate)) {
            $adapter->insertOnDuplicate(
                $this->resourceConnection->getTableName('directory_country_region'),
                $countryRegionDataToUpdate,
                ['code', 'default_name']
            );
        }

        if (!empty($countryRegionNameDataToUpdate)) {
            $adapter->insertOnDuplicate(
                $this->resourceConnection->getTableName('directory_country_region_name'),
                $countryRegionNameDataToUpdate,
                ['name']
            );
        }
    }
}
