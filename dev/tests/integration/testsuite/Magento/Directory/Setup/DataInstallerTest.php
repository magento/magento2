<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Setup;

use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Directory\Setup\DataInstaller;
use Magento\Framework\App\ResourceConnection;

/**
 * Provide test for DataInstaller
 */
class DataInstallerTest extends TestCase
{
    /**
     * @var DataInstaller
     */
    private $dataInstaller;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->dataInstaller = $objectManager->create(DataInstaller::class);
        $this->resourceConnection = $objectManager->create(ResourceConnection::class);
    }

    /**
     * @return void
     */
    public function testAddCountryRegions(): void
    {
        $adapter = $this->resourceConnection->getConnection();

        $regionsBefore = $this->getTableRowsCount('directory_country_region');
        $regionsNamesBefore = $this->getTableRowsCount('directory_country_region_name');

        $this->dataInstaller->addCountryRegions(
            $adapter,
            $this->getDataForRegions()
        );

        $regionsAfter = $this->getTableRowsCount('directory_country_region');
        $regionsNamesAfter = $this->getTableRowsCount('directory_country_region_name');

        $this->assertEquals(4, ($regionsAfter - $regionsBefore));
        $this->assertEquals(4, ($regionsNamesAfter - $regionsNamesBefore));
    }

    /**
     * Count table rows
     *
     * @param string $tableName
     * @return int
     */
    private function getTableRowsCount(string $tableName): int
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            $this->resourceConnection->getTableName($tableName),
            ['count(*)']
        );

        return (int)$connection->fetchOne($select);
    }

    /**
     * Test updating country region codes and names
     *
     * @return void
     */
    public function testUpdateCountryRegionCodes(): void
    {
        $adapter = $this->resourceConnection->getConnection();
        $countryCode = 'DE';

        // First, add test regions that we'll update
        $testRegions = [
            [$countryCode, 'OLD-01', 'Old Region 1'],
            [$countryCode, 'OLD-02', 'Old Region 2'],
            [$countryCode, 'OLD-03', 'Old Region 3'],
        ];

        $this->dataInstaller->addCountryRegions($adapter, $testRegions);

        // Verify regions were added
        $this->assertRegionExists($countryCode, 'OLD-01', 'Old Region 1');
        $this->assertRegionExists($countryCode, 'OLD-02', 'Old Region 2');
        $this->assertRegionExists($countryCode, 'OLD-03', 'Old Region 3');

        // Define code and name mappings for updates
        $codeMapping = [
            'OLD-01' => 'NEW-01',
            'OLD-02' => 'NEW-02',
            'OLD-03' => 'NEW-03',
        ];

        $nameMapping = [
            'OLD-01' => 'Updated Region 1',
            'OLD-02' => 'Updated Region 2',
            // OLD-03 name should remain unchanged
        ];

        // Update region codes and names
        $this->dataInstaller->updateCountryRegionCodes(
            $adapter,
            $countryCode,
            $codeMapping,
            $nameMapping
        );

        // Verify old codes no longer exist
        $this->assertRegionNotExists($countryCode, 'OLD-01');
        $this->assertRegionNotExists($countryCode, 'OLD-02');
        $this->assertRegionNotExists($countryCode, 'OLD-03');

        // Verify new codes exist with correct names
        $this->assertRegionExists($countryCode, 'NEW-01', 'Updated Region 1');
        $this->assertRegionExists($countryCode, 'NEW-02', 'Updated Region 2');
        $this->assertRegionExists($countryCode, 'NEW-03', 'Old Region 3'); // Name unchanged
    }

    /**
     * Assert that a region exists with given code and name
     *
     * @param string $countryCode
     * @param string $regionCode
     * @param string $expectedName
     * @return void
     */
    private function assertRegionExists(string $countryCode, string $regionCode, string $expectedName): void
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('directory_country_region'), ['default_name'])
            ->where('country_id = ?', $countryCode)
            ->where('code = ?', $regionCode);

        $actualName = $connection->fetchOne($select);
        $this->assertEquals(
            $expectedName,
            $actualName,
            "Region {$countryCode}-{$regionCode} should exist with name '{$expectedName}'"
        );

        // Also verify in region_name table
        $regionIdSelect = $connection->select()
            ->from($this->resourceConnection->getTableName('directory_country_region'), ['region_id'])
            ->where('country_id = ?', $countryCode)
            ->where('code = ?', $regionCode);

        $regionId = $connection->fetchOne($regionIdSelect);
        $this->assertNotNull($regionId, "Region ID should exist for {$countryCode}-{$regionCode}");

        $nameSelect = $connection->select()
            ->from($this->resourceConnection->getTableName('directory_country_region_name'), ['name'])
            ->where('region_id = ?', $regionId)
            ->where('locale = ?', 'en_US');

        $nameInTable = $connection->fetchOne($nameSelect);
        $this->assertEquals(
            $expectedName,
            $nameInTable,
            "Region name in directory_country_region_name should be '{$expectedName}'"
        );
    }

    /**
     * Assert that a region does not exist
     *
     * @param string $countryCode
     * @param string $regionCode
     * @return void
     */
    private function assertRegionNotExists(string $countryCode, string $regionCode): void
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('directory_country_region'), ['code'])
            ->where('country_id = ?', $countryCode)
            ->where('code = ?', $regionCode);

        $result = $connection->fetchOne($select);
        $this->assertFalse($result, "Region {$countryCode}-{$regionCode} should not exist");
    }

    /**
     * Return test data for new regions
     *
     * @return array[]
     */
    private function getDataForRegions(): array
    {
        return [
            ['JP', 'JP-01', 'State1'],
            ['JP', 'JP-02', 'State2'],
            ['JP', 'JP-03', 'State3'],
            ['UA', 'UA-410', 'State4'],
        ];
    }
}
