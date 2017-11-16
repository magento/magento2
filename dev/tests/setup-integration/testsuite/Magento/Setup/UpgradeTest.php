<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * The purpose of this test is verifying initial UpgradeSchema, UpgradeData scripts
 */
class UpgradeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\Deploy\CliCommand
     */
    private $shellCommand;

    /**
     * @var \Magento\TestFramework\Deploy\TestModuleManager
     */
    private $testModuleManager;

    /**
     * @var string
     */
    private static $moduleName = 'TestSetupModule2';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var array
     */
    private $tablesName = [];

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $installer;

    /**
     * @var array
     */
    private $expectedData = [];

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->shellCommand = $objectManager->create(\Magento\TestFramework\Deploy\CliCommand::class);
        $this->testModuleManager = $objectManager->create(\Magento\TestFramework\Deploy\TestModuleManager::class);
        /** @var \Magento\Framework\Setup\ModuleDataSetupInterface $installer */
        $this->installer = $objectManager->create(
            \Magento\Framework\Setup\ModuleDataSetupInterface::class
        );
        $this->connection = $this->installer->getConnection();
        $this->getTableNameMapping($this->installer);
    }

    /**
     * Install test module before test execution
     */
    public static function setUpBeforeClass()
    {
        $shellCommand = Bootstrap::getObjectManager()->create(\Magento\TestFramework\Deploy\CliCommand::class);
        self::assertContains(
            'The following modules have been enabled:',
            $shellCommand->introduceModule(self::$moduleName)
        );
        self::assertContains('Magento_' . self::$moduleName, $shellCommand->upgrade());
    }

    /**
     * Disable and remove test module files
     */
    public static function tearDownAfterClass()
    {
        $objectManager = Bootstrap::getObjectManager();
        $shellCommand = $objectManager->create(\Magento\TestFramework\Deploy\CliCommand::class);

        $shellCommand->disableModule(self::$moduleName);
        $testEnv = $objectManager->create(\Magento\TestFramework\Deploy\TestModuleManager::class);
        $testEnv->removeModuleFiles(self::$moduleName);
    }

    /**
     * @param string $initialName
     * @param string $name
     * @dataProvider tablesStructureDataProvider
     */
    public function testInstallSchema($initialName, $name)
    {
        $this->assertTrue($this->connection->isTableExists($name), "Table {$initialName} doesn't not exists");
    }

    /**
     * @param string $initialName
     * @param string $name
     * @dataProvider tablesStructureDataProvider
     */
    public function testTablesStructure($initialName, $name)
    {
        list($expectedSchema, $expectedRelations, $expectedIndexes) = $this->getExpectedData();
        //check if table prefix was defined
        $this->assertTrue($this->connection->isTableExists($name), "Table {$name} doesn't not exists");
        $tableSchema = $this->connection->describeTable($name);
        $this->assertEquals(
            $expectedSchema[$initialName],
            $this->getConvertedStructure($tableSchema),
            "Table structure doesn't match expected data for $name"
        );
        $actualForeignKeyName = $this->connection->getForeignKeys($name);
        $this->assertEquals(
            $expectedRelations[$initialName],
            $this->getForeignKeysData($actualForeignKeyName),
            "Relation data for {$name} table is corrupted"
        );
        $actualIndexList = $this->connection->getIndexList($name);
        $this->assertEquals(
            $expectedIndexes[$initialName],
            $this->getIndexesData($actualIndexList),
            "Indexes data for {$name} table is corrupted"
        );
    }

    public function tablesStructureDataProvider()
    {
        $returnData = [];
        $installer = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Setup\ModuleDataSetupInterface::class
        );
        foreach ($this->getTableNameMapping($installer) as $initialName => $name) {
            $returnData[] = [$initialName, $name];
        }
        return $returnData;
    }

    /**
     * @param string $tableName
     * @dataProvider tableDataAfterInstallDataProvider
     */
    public function testTableDataAfterInstall($tableName)
    {
        $expectedData = $expectedData = require __DIR__ . '/_files/expectedData.php';
        $name = $this->tablesName[$tableName];
        $this->assertTrue($this->connection->isTableExists($name), "Table {$name} doesn't not exists");
        $select = $this->connection->select()->from($this->installer->getTable($name));
        $dataFromTable = $this->connection->fetchAll($select);
        $this->assertEquals(1, count($dataFromTable));
        $this->assertEquals($expectedData[$tableName], $dataFromTable);
    }

    public function tableDataAfterInstallDataProvider()
    {
        return [
            ['setup_tests_entity_table'],
            ['setup_tests_address_entity']
        ];
    }

    public function testUpgrade()
    {
        list($expectedSchema, $expectedRelations, $expectedIndexes) = $this->getExpectedData();
        $this->testModuleManager->updateModuleFiles(self::$moduleName);
        $this->shellCommand->upgrade();
        $this->connection->resetDdlCache();
        $newTableName = $this->installer->getTable('setup_tests_entity_passwords');
        $this->assertTrue(
            $this->connection->isTableExists($newTableName),
            "Table {$newTableName} doesn't not exists"
        );
        $actualIndexesList = $this->connection->getIndexList($newTableName);
        $actualRelations = $this->connection->getForeignKeys($newTableName);
        $actualTableStructure = $this->connection->describeTable($newTableName);
        $this->assertEquals(
            $expectedSchema['setup_tests_entity_passwords'],
            $this->getConvertedStructure($actualTableStructure),
            "Table structure doesn't match expected data for {$newTableName}"
        );
        $this->assertEquals(
            $expectedIndexes['setup_tests_entity_passwords'],
            $this->getIndexesData($actualIndexesList),
            "Indexes data for $newTableName table is corrupted"
        );
        $this->assertEquals(
            $expectedRelations['setup_tests_entity_passwords'],
            $this->getForeignKeysData($actualRelations),
            "Relation data for $newTableName table is corrupted"
        );
        $dateTimeTable = $this->tablesName['setup_tests_address_entity_datetime'];
        $this->assertFalse(
            $this->connection->isTableExists($dateTimeTable),
            "Table $dateTimeTable not exists"
        );
        $this->assertFalse(
            $this->connection->tableColumnExists($this->tablesName['setup_tests_entity_table'], 'dob'),
            'Column \'dob\' exists after upgrade'
        );
        $this->assertEmpty(
            $this->connection->getForeignKeys($this->tablesName['setup_tests_address_entity_decimal']),
            'Foreign key hasn\'t been removed after upgrade'
        );
        $indexesList = $this->connection->getIndexList($this->tablesName['setup_tests_address_entity_decimal']);
        $this->assertEquals(3, count($indexesList), 'Index hasn\'t been removed after upgrade');
    }

    /**
     * @param string $tableName
     * @param int $count
     * @dataProvider tableDataAfterUpgradeDataProvider
     */
    public function testDataAfterUpgrade($count, $tableName)
    {
        $expectedData = $expectedData = require __DIR__ . '/_files/expectedDataAfterUpgrade.php';
        $name = $this->installer->getTable($tableName);
        $this->assertTrue($this->connection->isTableExists($name), "Table {$name} doesn't not exists");
        $select = $this->connection->select()->from($name);
        $dataFromTable = $this->connection->fetchAll($select);
        $this->assertEquals($count, count($dataFromTable));
        $this->assertEquals($expectedData[$tableName], $dataFromTable);
    }

    public function tableDataAfterUpgradeDataProvider()
    {
        return [
            [1, 'setup_tests_entity_table'],
            [2, 'setup_tests_address_entity'],
            [1, 'setup_tests_entity_passwords']
        ];
    }

    /**
     * Convert actual foreign keys data
     *
     * @param array $actualRelations
     * @return array
     */
    private function getForeignKeysData($actualRelations)
    {
        $actualForeignKey = [];
        if (!empty($actualRelations)) {
            $foreignKeyData = array_shift($actualRelations);
            $actualForeignKey = [
                'COLUMN_NAME' => $foreignKeyData['COLUMN_NAME'],
                'REF_COLUMN_NAME' => $foreignKeyData['REF_COLUMN_NAME'],
                'ON_DELETE' => $foreignKeyData['ON_DELETE'],
            ];
        }
        return $actualForeignKey;
    }

    /**
     * Convert actual indexes data
     *
     * @param array $actualIndexesList
     * @return array
     */
    private function getIndexesData($actualIndexesList)
    {
        $actualData = [];
        foreach ($actualIndexesList as $indexData) {
            $actualData[] = [
                'COLUMNS_LIST' => $indexData['COLUMNS_LIST'],
                'INDEX_TYPE' => $indexData['INDEX_TYPE'],
                'INDEX_METHOD' => $indexData['INDEX_METHOD'],
                'type' => $indexData['type'],
                'fields' => $indexData['fields'],
            ];
        }
        return $actualData;
    }

    /**
     * Convert actual table schema structure
     *
     * @param array $actualTableStructure
     * @return array
     */
    private function getConvertedStructure($actualTableStructure)
    {
        $actualSchema = [];
        foreach ($actualTableStructure as $columnName => $columnData) {
            unset($columnData['TABLE_NAME']);
            $actualSchema[$columnName] = $columnData;
        }
        return $actualSchema;
    }

    /**
     * Return expected data loaded from file
     *
     * @return array
     */
    private function getExpectedData()
    {
        if (empty($this->expectedData)) {
            $expectedSchema = require __DIR__ . '/_files/expectedSchema.php';
            $expectedRelations = require __DIR__ . '/_files/expectedRelations.php';
            $expectedIndexes = require __DIR__ . '/_files/expectedIndexes.php';
            $this->expectedData = [
                $expectedSchema,
                $expectedRelations,
                $expectedIndexes
            ];
        }
        return $this->expectedData;
    }

    /**
     * Return array of table names in format 'initial table name' => 'table name with prefix'
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $installer
     * @return array
     */
    private function getTableNameMapping($installer)
    {

        if (empty($this->tablesName)) {
            $installedTableNames = [
                'setup_tests_entity_table',
                'setup_tests_address_entity',
                'setup_tests_address_entity_datetime',
                'setup_tests_address_entity_decimal'
            ];
            foreach ($installedTableNames as $name) {
                $this->tablesName[$name] = $installer->getTable($name);
            }
        }
        return $this->tablesName;
    }
}
