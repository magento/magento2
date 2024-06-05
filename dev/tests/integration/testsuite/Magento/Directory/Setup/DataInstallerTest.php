<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $expectedCountries = $this->getCountries(true);

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
        $this->assertEquals($expectedCountries, $this->getCountries());
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
     * Return required countries with regions
     *
     * @param bool $isConfig
     * @return string
     */
    private function getCountries(bool $isConfig = false): string
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('core_config_data'), 'value')
            ->where('path = ?', 'general/region/state_required')
            ->where('scope = ?', 'default')
            ->where('scope_id = ?', 0);

        $countries = $connection->fetchOne($select);
        $countries = (!empty($countries)) ? explode(',', $countries) : [];

        if (!$isConfig) {
            return implode(',', $countries);
        }

        $countryCodes = ['JP', 'UA'];
        foreach ($countryCodes as $country) {
            if (!in_array($country, $countries)) {
                $countries[] = $country;
            }
        }

        return implode(',', $countries);
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
