<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Setup\Patch\Data;

use Magento\Directory\Setup\DataInstaller;
use Magento\Directory\Setup\DataInstallerFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Update Latvian regions to match the current administrative-territorial
 * structure: 7 state cities + 35 municipalities established by the
 * 2021 reform and subsequent 2025 Varakļāni merger.
 *
 * Surviving regions have their codes updated to current ISO 3166-2:LV,
 * disbanded municipalities are removed, and new entities are inserted.
 *
 * @see https://lv.wikipedia.org/wiki/Latvijas_administrat%C4%ABvais_iedal%C4%ABjums
 * @see https://en.wikipedia.org/wiki/ISO_3166-2:LV
 */
class UpdateRegionsForLatvia implements DataPatchInterface
{
    private const string COUNTRY_CODE = 'LV';

    /**
     * Old region code → new ISO 3166-2:LV code for regions that survived the reform.
     * State cities (LV-DGV, LV-JEL, LV-JUR, LV-LPX, LV-REZ, LV-RIX, LV-VEN)
     * already have correct codes and need no update.
     *
     * @var array<string, string>
     */
    private const array CODE_UPDATES = [
        // Old district codes → new municipality codes
        'LV-AI' => 'LV-002',
        'LV-AL' => 'LV-007',
        'LV-BL' => 'LV-015',
        'LV-BU' => 'LV-016',
        'LV-CE' => 'LV-022',
        'LV-DO' => 'LV-026',
        'LV-GU' => 'LV-033',
        'LV-JK' => 'LV-042',
        'LV-JL' => 'LV-041',
        'LV-KR' => 'LV-047',
        'LV-KU' => 'LV-050',
        'LV-LM' => 'LV-054',
        'LV-LU' => 'LV-058',
        'LV-MA' => 'LV-059',
        'LV-OG' => 'LV-067',
        'LV-PR' => 'LV-073',
        'LV-RE' => 'LV-077',
        'LV-SA' => 'LV-088',
        'LV-TA' => 'LV-097',
        'LV-TU' => 'LV-099',
        'LV-VK' => 'LV-101',
        'LV-VM' => 'LV-113',
        'LV-VE' => 'LV-106',
        // Name-as-code entries → proper ISO codes
        'Līvānu novads'    => 'LV-056',
        'Mārupes novads'   => 'LV-062',
        'Olaines novads'   => 'LV-068',
        'Ropažu novads'    => 'LV-080',
        'Salaspils novads' => 'LV-087',
        'Saulkrastu novads' => 'LV-089',
        'Siguldas novads'  => 'LV-091',
        'Smiltenes novads' => 'LV-094',
        'Ādažu novads'     => 'LV-011',
        'Ķekavas novads'   => 'LV-052',
    ];

    /**
     * Complete set of valid codes after the reform.
     * Used to identify and remove disbanded regions after code updates.
     *
     * @var array<string>
     */
    private const array VALID_CODES = [
        // State cities
        'LV-DGV', 'LV-JEL', 'LV-JUR', 'LV-LPX', 'LV-REZ', 'LV-RIX', 'LV-VEN',
        // Municipalities
        'LV-002', 'LV-007', 'LV-011', 'LV-015', 'LV-016', 'LV-022', 'LV-026',
        'LV-033', 'LV-041', 'LV-042', 'LV-047', 'LV-050', 'LV-052', 'LV-054',
        'LV-056', 'LV-058', 'LV-059', 'LV-062', 'LV-067', 'LV-068', 'LV-073',
        'LV-077', 'LV-080', 'LV-087', 'LV-088', 'LV-089', 'LV-091', 'LV-094',
        'LV-097', 'LV-099', 'LV-101', 'LV-106', 'LV-111', 'LV-112', 'LV-113',
    ];

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param DataInstallerFactory $dataInstallerFactory
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly DataInstallerFactory $dataInstallerFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(): UpdateRegionsForLatvia
    {
        $connection = $this->moduleDataSetup->getConnection();
        $regionTable = $this->moduleDataSetup->getTable('directory_country_region');

        $this->updateRegionCodes($connection, $regionTable);
        $this->deleteDefunctRegions($connection, $regionTable);
        $this->addNewRegions($connection);

        return $this;
    }

    /**
     * Update ISO codes for regions that survived the reform but had outdated codes.
     * Preserves region_id so existing customer addresses remain valid.
     *
     * @param AdapterInterface $connection
     * @param string $regionTable
     * @return void
     */
    private function updateRegionCodes(AdapterInterface $connection, string $regionTable): void
    {
        foreach (self::CODE_UPDATES as $oldCode => $newCode) {
            $connection->update(
                $regionTable,
                ['code' => $newCode],
                [
                    'country_id = ?' => self::COUNTRY_CODE,
                    'code = ?' => $oldCode,
                ]
            );
        }
    }

    /**
     * Remove regions that no longer exist after the 2021 reform.
     * CASCADE FK on directory_country_region_name handles locale entries.
     *
     * @param AdapterInterface $connection
     * @param string $regionTable
     * @return void
     */
    private function deleteDefunctRegions(AdapterInterface $connection, string $regionTable): void
    {
        $connection->delete(
            $regionTable,
            [
                'country_id = ?' => self::COUNTRY_CODE,
                'code NOT IN (?)' => self::VALID_CODES,
            ]
        );
    }

    /**
     * Insert municipalities created by the 2021 reform with no predecessor.
     *
     * @param AdapterInterface $connection
     * @return void
     */
    private function addNewRegions(AdapterInterface $connection): void
    {
        /** @var DataInstaller $dataInstaller */
        $dataInstaller = $this->dataInstallerFactory->create();
        $dataInstaller->addCountryRegions($connection, [
            [self::COUNTRY_CODE, 'LV-111', 'Augšdaugavas novads'],
            [self::COUNTRY_CODE, 'LV-112', 'Dienvidkurzemes novads'],
        ]);
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
}
