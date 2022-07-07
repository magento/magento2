<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\AppInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Update region names for Switzerland.
 */
class UpdateRegionNamesForSwitzerland implements DataPatchInterface
{
    public const SWITZERLAND_COUNTRY_CODE = 'CH';
    /**
     * Array structure:
     * - key - region code (like the 'code' field in the 'directory_country_region' table)
     * - value - new default name for the region (like the 'default_name' field in the 'directory_country_region' table)
     * @var array
     */
    public const SWITZERLAND_COUNTRY_REGION_DATA_TO_UPDATE = [
        'FR' => 'Friburg',
        'GE' => 'Geneva',
        'LU' => 'Lucerne',
        'NE' => 'Neuchâtel',
        'TI' => 'Ticino',
        'VD' => 'Vaud',
    ];

    private const REGION_KEY_REGION_ID = 'region_id';
    private const REGION_KEY_COUNTRY_ID = 'country_id';
    private const REGION_KEY_CODE = 'code';
    private const REGION_KEY_DEFAULT_NAME = 'default_name';

    private const REGION_NAME_REGION_ID = 'region_id';
    private const REGION_NAME_KEY_LOCALE = 'locale';
    private const REGION_NAME_KEY_NAME = 'name';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param RegionCollectionFactory $regionCollectionFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        RegionCollectionFactory  $regionCollectionFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function apply(): DataPatchInterface
    {
        $regionItems = $this->getRegionItemsToUpdate();
        if (!count($regionItems)) {
            return $this;
        }

        $countryRegionDataToUpdate = [];
        $countryRegionNameDataToUpdate = [];
        $connection = $this->moduleDataSetup->getConnection();
        foreach ($regionItems as $regionItem) {
            $code = $regionItem->getData(self::REGION_KEY_CODE);
            $newRegionName = self::SWITZERLAND_COUNTRY_REGION_DATA_TO_UPDATE[$code] ?? null;
            if ($newRegionName === null) {
                continue;
            }
            // Collect data to insert into the 'directory_country_region' table
            $countryRegionDataToUpdate[] = [
                self::REGION_KEY_REGION_ID => $regionItem->getData(self::REGION_KEY_REGION_ID),
                self::REGION_KEY_COUNTRY_ID => $regionItem->getData(self::REGION_KEY_COUNTRY_ID),
                self::REGION_KEY_CODE => $code,
                self::REGION_KEY_DEFAULT_NAME => $newRegionName,
            ];
            // Collect data to insert into the 'directory_country_region_name' table
            $countryRegionNameDataToUpdate[] = [
                self::REGION_NAME_KEY_LOCALE => AppInterface::DISTRO_LOCALE_CODE,
                self::REGION_NAME_REGION_ID => $regionItem->getData(self::REGION_KEY_REGION_ID),
                self::REGION_NAME_KEY_NAME => $newRegionName
            ];
        }

        // Update region tables with new region names
        $connection->insertOnDuplicate(
            $this->moduleDataSetup->getTable('directory_country_region'),
            $countryRegionDataToUpdate,
            [self::REGION_KEY_DEFAULT_NAME]
        );
        $connection->insertOnDuplicate(
            $this->moduleDataSetup->getTable('directory_country_region_name'),
            $countryRegionNameDataToUpdate,
            [self::REGION_NAME_KEY_NAME]
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
            InitializeDirectoryData::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * Get region items filtered by 'CH' country and region codes (data to update).
     *
     * @return DataObject[]
     */
    private function getRegionItemsToUpdate(): array
    {
        $regionCollection = $this->regionCollectionFactory->create();
        $regionCollection->addCountryFilter(self::SWITZERLAND_COUNTRY_CODE);
        $regionCollection->addRegionCodeFilter(array_keys(self::SWITZERLAND_COUNTRY_REGION_DATA_TO_UPDATE));

        return $regionCollection->getItems();
    }
}
