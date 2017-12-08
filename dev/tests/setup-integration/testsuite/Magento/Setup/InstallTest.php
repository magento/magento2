<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * The purpose of this test is verifying initial InstallSchema, InstallData scripts
 */
class InstallTest extends \PHPUnit\Framework\TestCase
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
    private static $moduleName = 'TestSetupModule1';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var array
     */
    private $tablesName = [];

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->shellCommand = $objectManager->create(\Magento\TestFramework\Deploy\CliCommand::class);
        $this->testModuleManager = $objectManager->create(\Magento\TestFramework\Deploy\TestModuleManager::class);
        /** @var \Magento\Framework\Setup\ModuleDataSetupInterface $installer */
        $installer = $objectManager->create(
            \Magento\Framework\Setup\ModuleDataSetupInterface::class
        );
        $this->connection = $installer->getConnection();
    }

    public static function tearDownAfterClass()
    {
        $objectManager = Bootstrap::getObjectManager();
        $shellCommand = $objectManager->create(\Magento\TestFramework\Deploy\CliCommand::class);

        $shellCommand->disableModule(self::$moduleName);
        $testEnv = $objectManager->create(\Magento\TestFramework\Deploy\TestModuleManager::class);
        $testEnv->removeModuleFiles(self::$moduleName);
    }

    public function testInstallSchema()
    {
        $moduleName = self::$moduleName;
        //check that table not exists
        foreach ($this->getTableNameMapping() as $name) {
            $this->assertFalse($this->connection->isTableExists($name), "Table {$name} already exists");
        }
        $this->assertContains(
            'The following modules have been enabled:',
            $this->shellCommand->introduceModule($moduleName)
        );
        $this->assertContains("Module 'Magento_$moduleName':", $this->shellCommand->upgrade());
        //check that table exists and have corresponded columns
        foreach ($this->getTableNameMapping() as $name) {
            $this->assertTrue($this->connection->isTableExists($name), "Table {$name} doesn't not exists");
        }
    }

    /**
     * @param string $initialName
     * @param string $name
     * @dataProvider getAffectedTables
     */
    public function testColumnStructure($initialName, $name)
    {
        $expectedSchema = require __DIR__ . '/_files/expectedSchema.php';
        //check if table prefix was defined
        $this->assertTrue($this->connection->isTableExists($name), "Table {$name} doesn't not exists");
        $tableSchema = $this->connection->describeTable($name);
        $actualSchema = [];
        foreach ($tableSchema as $columnName => $columnData) {
            unset($columnData['TABLE_NAME']);
            $actualSchema[$columnName] = $columnData;
        }
        $this->assertEquals($expectedSchema[$initialName], $actualSchema);
    }

    /**
     * @param string $initialName
     * @param string $name
     * @dataProvider getAffectedTables
     */
    public function testVerifyRelations($initialName, $name)
    {
        $expectedRelations = require __DIR__ . '/_files/expectedRelations.php';
        //check that table not exists
        $this->assertTrue($this->connection->isTableExists($name), "Table {$name} doesn't not exists");
        $actualForeignKeyName = $this->connection->getForeignKeys($name);
        $actualData = [];
        if (!empty($actualForeignKeyName)) {
            $foreignKeyData = array_shift($actualForeignKeyName);
            $actualData = [
                'COLUMN_NAME' => $foreignKeyData['COLUMN_NAME'],
                'REF_COLUMN_NAME' => $foreignKeyData['REF_COLUMN_NAME'],
                'ON_DELETE' => $foreignKeyData['ON_DELETE'],
            ];
        }
        $this->assertEquals(
            $expectedRelations[$initialName],
            $actualData,
            "Relation data for $name table is corrupted"
        );
    }

    /**
     * @param string $initialName
     * @param string $name
     * @dataProvider getAffectedTables
     */
    public function testVerifyIndexes($initialName, $name)
    {
        $expectedIndexes = require __DIR__ . '/_files/expectedIndexes.php';
        //check that table not exists
        $this->assertTrue($this->connection->isTableExists($name), "Table {$name} doesn't not exists");
        $actualIndexList = $this->connection->getIndexList($name);
        $actualData = [];
        if (!empty($actualIndexList)) {
            foreach ($actualIndexList as $indexData) {
                $actualData[] = [
                    'COLUMNS_LIST' => $indexData['COLUMNS_LIST'],
                    'INDEX_TYPE' => $indexData['INDEX_TYPE'],
                    'INDEX_METHOD' => $indexData['INDEX_METHOD'],
                    'type' => $indexData['type'],
                    'fields' => $indexData['fields'],
                ];
            }
        }
        $this->assertEquals(
            $expectedIndexes[$initialName],
            $actualData,
            "Indexes data for $name table is corrupted"
        );
    }

    /**
     * @return array
     */
    public function getAffectedTables()
    {
        $returnData = [];
        foreach ($this->getTableNameMapping() as $initialName => $name) {
            $returnData[] = [$initialName, $name];
        }
        return $returnData;
    }

    /**
     * Return array of table names in format 'initial table name' => 'table name with prefix'
     *
     * @return array
     */
    private function getTableNameMapping()
    {
        $installer = Bootstrap::getObjectManager()->create(
            \Magento\Framework\Setup\ModuleDataSetupInterface::class
        );
        if (empty($this->tablesName)) {
            $installedTableNames = [
                'setup_tests_table1',
                'setup_tests_table1_related'
            ];
            foreach ($installedTableNames as $name) {
                $this->tablesName[$name] = $installer->getTable($name);
            }
        }
        return $this->tablesName;
    }
}
