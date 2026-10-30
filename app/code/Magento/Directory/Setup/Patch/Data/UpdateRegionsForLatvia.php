<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Migrate Latvian regions disbanded by the 2021 administrative-territorial reform
 * and the 2025 merger of Varakļāni municipality into Madona municipality.
 *
 * Runs after AddDataForLatviaV1 (adds LV-111, LV-112) and UpdateRegionCodesForLatviaV1
 * (remaps surviving regions to ISO 3166-2:LV codes), so it only handles what those leave behind:
 * each obsolete region shipped by InitializeDirectoryData has its address references moved to
 * the successor municipality before it is deleted. Regions not listed here (e.g. custom regions)
 * are never touched, and obsolete regions still used by tax rates or table rates are kept.
 *
 * @see https://likumi.lv/ta/id/315654
 * @see https://en.wikipedia.org/wiki/ISO_3166-2:LV
 */
class UpdateRegionsForLatvia implements DataPatchInterface
{
    private const string COUNTRY_CODE = 'LV';

    /**
     * Obsolete region code => successor region code (null when there is no single successor).
     *
     * @var array<string, string|null>
     */
    private const array OBSOLETE_REGIONS = [
        // Former republic cities merged into their municipalities
        'Jēkabpils' => 'LV-042',
        'Valmiera' => 'LV-113',
        // Former districts
        'LV-DA' => 'LV-111',
        'LV-LE' => 'LV-112',
        'LV-RI' => null,
        // Merged into Madonas novads in 2025
        'LV-102' => 'LV-059',
        // Municipalities merged in 2021
        'Aglonas novads' => 'LV-073',
        'Aizputes novads' => 'LV-112',
        'Aknīstes novads' => 'LV-042',
        'Alojas novads' => 'LV-054',
        'Alsungas novads' => 'LV-050',
        'Amatas novads' => 'LV-022',
        'Apes novads' => 'LV-094',
        'Auces novads' => 'LV-026',
        'Babītes novads' => 'LV-062',
        'Baldones novads' => 'LV-052',
        'Baltinavas novads' => 'LV-015',
        'Beverīnas novads' => 'LV-113',
        'Brocēnu novads' => 'LV-088',
        'Burtnieku novads' => 'LV-113',
        'Carnikavas novads' => 'LV-011',
        'Cesvaines novads' => 'LV-059',
        'Ciblas novads' => 'LV-058',
        'Dagdas novads' => 'LV-047',
        'Dundagas novads' => 'LV-097',
        'Durbes novads' => 'LV-112',
        'Engures novads' => 'LV-099',
        'Garkalnes novads' => 'LV-080',
        'Grobiņas novads' => 'LV-112',
        'Iecavas novads' => 'LV-016',
        'Ikšķiles novads' => 'LV-067',
        'Ilūkstes novads' => 'LV-111',
        'Inčukalna novads' => 'LV-091',
        'Jaunjelgavas novads' => 'LV-002',
        'Jaunpiebalgas novads' => 'LV-022',
        'Jaunpils novads' => 'LV-099',
        'Kandavas novads' => 'LV-099',
        'Kokneses novads' => 'LV-002',
        'Krimuldas novads' => 'LV-091',
        'Krustpils novads' => 'LV-042',
        'Kārsavas novads' => 'LV-058',
        'Lielvārdes novads' => 'LV-067',
        'Lubānas novads' => 'LV-059',
        'Līgatnes novads' => 'LV-022',
        'Mazsalacas novads' => 'LV-113',
        'Mālpils novads' => 'LV-091',
        'Naukšēnu novads' => 'LV-113',
        'Neretas novads' => 'LV-002',
        'Nīcas novads' => 'LV-112',
        'Ozolnieku novads' => 'LV-041',
        'Priekules novads' => 'LV-112',
        'Priekuļu novads' => 'LV-022',
        'Pārgaujas novads' => 'LV-022',
        'Pāvilostas novads' => 'LV-112',
        'Pļaviņu novads' => 'LV-002',
        'Raunas novads' => 'LV-094',
        'Riebiņu novads' => 'LV-073',
        'Rojas novads' => 'LV-097',
        'Rucavas novads' => 'LV-112',
        'Rugāju novads' => 'LV-015',
        'Rundāles novads' => 'LV-016',
        'Rūjienas novads' => 'LV-113',
        'Salacgrīvas novads' => 'LV-054',
        'Salas novads' => 'LV-042',
        'Skrundas novads' => 'LV-050',
        'Skrīveru novads' => 'LV-002',
        'Stopiņu novads' => 'LV-080',
        'Strenču novads' => 'LV-113',
        'Sējas novads' => 'LV-089',
        'Tērvetes novads' => 'LV-026',
        'Vaiņodes novads' => 'LV-112',
        'Vecpiebalgas novads' => 'LV-022',
        'Vecumnieku novads' => 'LV-016',
        'Viesītes novads' => 'LV-042',
        'Viļakas novads' => 'LV-015',
        'Viļānu novads' => 'LV-077',
        'Vārkavas novads' => 'LV-073',
        'Zilupes novads' => 'LV-058',
        'Ērgļu novads' => 'LV-059',
        'Ķeguma novads' => 'LV-067',
    ];

    /**
     * Region code => [name shipped by AddDataForLatviaV1, corrected name].
     *
     * @var array<string, array{string, string}>
     */
    private const array NAME_CORRECTIONS = [
        'LV-112' => ['Dienvidkurzemes Novads', 'Dienvidkurzemes novads'],
    ];

    /**
     * Address tables whose region references are remapped. The flag tells whether the
     * region name stored on the address is replaced too; order addresses keep the historical name.
     *
     * @var array<string, bool>
     */
    private const array ADDRESS_TABLES = [
        'customer_address_entity' => true,
        'quote_address' => true,
        'sales_order_address' => false,
    ];

    /**
     * Configuration tables referencing regions; obsolete regions used there are kept.
     *
     * @var array<string, string>
     */
    private const array CONFIGURATION_TABLES = [
        'tax_calculation_rate' => 'tax_region_id',
        'shipping_tablerate' => 'dest_region_id',
    ];

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(): DataPatchInterface
    {
        $connection = $this->moduleDataSetup->getConnection();
        $this->correctRegionNames($connection);
        $this->migrateObsoleteRegions($connection);

        return $this;
    }

    /**
     * Fix region names shipped with wrong casing, unless they were changed by the merchant.
     *
     * @param AdapterInterface $connection
     * @return void
     */
    private function correctRegionNames(AdapterInterface $connection): void
    {
        $regionTable = $this->moduleDataSetup->getTable('directory_country_region');
        $regionNameTable = $this->moduleDataSetup->getTable('directory_country_region_name');

        foreach (self::NAME_CORRECTIONS as $code => [$oldName, $newName]) {
            $regionId = $connection->fetchOne(
                $connection->select()
                    ->from($regionTable, ['region_id'])
                    ->where('country_id = ?', self::COUNTRY_CODE)
                    ->where('code = ?', $code)
            );
            if (!$regionId) {
                continue;
            }

            $connection->update(
                $regionTable,
                ['default_name' => $newName],
                ['region_id = ?' => (int)$regionId, 'default_name = ?' => $oldName]
            );
            $connection->update(
                $regionNameTable,
                ['name' => $newName],
                ['region_id = ?' => (int)$regionId, 'name = ?' => $oldName]
            );
        }
    }

    /**
     * Move address references from obsolete regions to their successors and delete the obsolete regions.
     *
     * @param AdapterInterface $connection
     * @return void
     */
    private function migrateObsoleteRegions(AdapterInterface $connection): void
    {
        $regionTable = $this->moduleDataSetup->getTable('directory_country_region');
        $regions = $connection->fetchAssoc(
            $connection->select()
                ->from($regionTable, ['code', 'region_id', 'default_name'])
                ->where('country_id = ?', self::COUNTRY_CODE)
        );
        $referencedRegionIds = $this->getRegionIdsUsedInConfiguration($connection);

        foreach (self::OBSOLETE_REGIONS as $obsoleteCode => $successorCode) {
            if (!isset($regions[$obsoleteCode])) {
                continue;
            }
            $obsoleteRegionId = (int)$regions[$obsoleteCode]['region_id'];
            if (in_array($obsoleteRegionId, $referencedRegionIds, true)) {
                continue;
            }

            $successor = null;
            if ($successorCode !== null) {
                $successor = $regions[$successorCode] ?? null;
                if ($successor === null) {
                    // Successor is missing (e.g. removed by the merchant); keep the obsolete region.
                    continue;
                }
            }

            $this->remapAddresses($connection, $obsoleteRegionId, $successor);
            // directory_country_region_name rows are removed by the ON DELETE CASCADE foreign key.
            $connection->delete($regionTable, ['region_id = ?' => $obsoleteRegionId]);
        }
    }

    /**
     * Point addresses at the successor region, or detach them when there is none.
     *
     * The region name stored on the address is left in place when detaching, so no address data is lost.
     *
     * @param AdapterInterface $connection
     * @param int $obsoleteRegionId
     * @param array|null $successor
     * @return void
     */
    private function remapAddresses(AdapterInterface $connection, int $obsoleteRegionId, ?array $successor): void
    {
        foreach (self::ADDRESS_TABLES as $table => $updateRegionName) {
            $tableName = $this->moduleDataSetup->getTable($table);
            if (!$connection->isTableExists($tableName)) {
                continue;
            }

            $bind = ['region_id' => $successor !== null ? (int)$successor['region_id'] : null];
            if ($successor !== null && $updateRegionName) {
                $bind['region'] = $successor['default_name'];
            }
            $connection->update(
                $tableName,
                $bind,
                ['country_id = ?' => self::COUNTRY_CODE, 'region_id = ?' => $obsoleteRegionId]
            );
        }
    }

    /**
     * Get Latvian region IDs used by tax rates or shipping table rates.
     *
     * @param AdapterInterface $connection
     * @return int[]
     */
    private function getRegionIdsUsedInConfiguration(AdapterInterface $connection): array
    {
        $regionIds = [];
        foreach (self::CONFIGURATION_TABLES as $table => $column) {
            $tableName = $this->moduleDataSetup->getTable($table);
            if (!$connection->isTableExists($tableName)) {
                continue;
            }
            $regionIds[] = $connection->fetchCol(
                $connection->select()
                    ->distinct()
                    ->from($tableName, [$column])
                    ->where($column . ' > 0')
            );
        }

        return array_map('intval', array_merge([], ...$regionIds));
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [
            InitializeDirectoryData::class,
            AddDataForLatviaV1::class,
            UpdateRegionCodesForLatviaV1::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
