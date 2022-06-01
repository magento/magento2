<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Diff\SchemaDiff;
use Magento\Framework\Setup\Declaration\Schema\SchemaConfigInterface;
use Magento\Framework\Setup\Declaration\Schema\Sharding;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\DescribeTable;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * The purpose of this test is verifying declarative installation works.
 */
class DeclarativeInstallerTest extends SetupTestCase
{
    /**
     * @var  TestModuleManager
     */
    private $moduleManager;

    /**
     * @var CliCommand
     */
    private $cliCommand;

    /**
     * @var SchemaDiff
     */
    private $schemaDiff;

    /**
     * @var SchemaConfigInterface
     */
    private $schemaConfig;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DescribeTable
     */
    private $describeTable;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommand = $objectManager->get(CliCommand::class);
        $this->describeTable = $objectManager->get(DescribeTable::class);
        $this->schemaDiff = $objectManager->get(SchemaDiff::class);
        $this->schemaConfig = $objectManager->get(SchemaConfigInterface::class);
        $this->resourceConnection = $objectManager->get(ResourceConnection::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/installation.php
     */
    public function testInstallation()
    {
        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule1']
        );

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );

        //Second time installation should not find anything as we do not change anything
        self::assertNull($diff->getAll());
        $this->compareStructures();
    }

    /**
     * Compare structure of DB and declared structure.
     */
    private function compareStructures()
    {
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        foreach ($this->getTrimmedData() as $tableName => $sql) {
            $this->assertArrayHasKey($tableName, $shardData);
            /**
             * MySQL 8.0 and above does not provide information about the ON DELETE instruction
             * if ON DELETE NO ACTION
             */
            if (preg_match('#ON DELETE\s+NO ACTION#i', $shardData[$tableName] === 1)) {
                preg_replace('#ON DELETE\s+NO ACTION#i', '', $sql);
                self::assertEquals($sql, $shardData[$tableName]);
            }
        }
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/column_modification.php
     * @throws \Exception
     */
    public function testInstallationWithColumnsModification()
    {
        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule1']
        );

        //Move InstallSchema file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'column_modifications',
            'db_schema.xml',
            'etc'
        );

        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule1']
        );

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->getAll());
        $this->compareStructures();
    }

    /**
     * Updates revision for db_schema and db_schema_whitelist files
     *
     * @param string $revisionName
     */
    private function updateDbSchemaRevision($revisionName)
    {
        //Move InstallSchema file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            $revisionName,
            'db_schema.xml',
            'etc'
        );
        //Move InstallSchema file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            $revisionName,
            'db_schema_whitelist.json',
            'etc'
        );
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/column_removal.php
     * @throws \Exception
     */
    public function testInstallationWithColumnsRemoval()
    {
        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule1']
        );
        $this->updateDbSchemaRevision('column_removals');
        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule1']
        );

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->getAll());
        $this->compareStructures();
    }

    /**
     * As sometimes we want to ignore spaces and other special characters,
     * we need to trim data before compare it
     *
     * @return array
     */
    private function getTrimmedData()
    {
        $data = [];
        foreach ($this->getData() as $key => $createTable) {
            $data[$key] = preg_replace('/(\s)\n/', '$1', $createTable);
        }

        return $data;
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/constraint_modification.php
     * @throws \Exception
     */
    public function testInstallationWithConstraintsModification()
    {
        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule1']
        );
        $this->updateDbSchemaRevision('constraint_modifications');
        $this->cliCommand->upgrade();

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->getAll());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        $this->assertTableCreationStatements($this->getTrimmedData(), $shardData);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/table_removal.php
     * @throws \Exception
     */
    public function testInstallationWithDroppingTables()
    {
        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule1']
        );

        //Move db_schema.xml file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'drop_table',
            'db_schema.xml',
            'etc'
        );

        $this->cliCommand->upgrade();

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->getAll());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        $this->assertTableCreationStatements($this->getData(), $shardData);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @moduleName Magento_TestSetupDeclarationModule3
     */
    public function testInstallationWithDroppingTablesFromSecondaryModule()
    {
        $modules = [
            'Magento_TestSetupDeclarationModule1',
            'Magento_TestSetupDeclarationModule3',
        ];

        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule3',
            'drop_table_with_external_dependency',
            'db_schema.xml',
            'etc'
        );

        foreach ($modules as $moduleName) {
            $this->moduleManager->updateRevision(
                $moduleName,
                'without_setup_version',
                'module.xml',
                'etc'
            );
        }

        try {
            $this->cliCommand->install($modules);
        } catch (\Exception $e) {
            $installException = $e->getPrevious();
            self::assertSame(1, $installException->getCode());
            self::assertStringContainsString(
                'The reference table named "reference_table" is disabled',
                $installException->getMessage()
            );
        }
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/rollback.php
     * @throws \Exception
     */
    public function testInstallWithCodeBaseRollback()
    {
        //Move db_schema.xml file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'before_rollback',
            'db_schema.xml',
            'etc'
        );
        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule1']
        );

        if ($this->isUsingAuroraDb()) {
            $this->markTestSkipped('Test skipped in AWS Aurora');
        }
        $beforeRollback = $this->describeTable->describeShard('default');
        self::assertEquals($this->getTrimmedData()['before'], $beforeRollback);
        //Move db_schema.xml file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'after_rollback',
            'db_schema.xml',
            'etc'
        );

        $this->cliCommand->upgrade();
        $afterRollback = $this->describeTable->describeShard('default');
        self::assertEquals($this->getData()['after'], $afterRollback);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/table_rename.php
     * @throws \Exception
     */
    public function testTableRename()
    {
        $dataToMigrate = ['some_column' => 'Some Value'];
        //Move db_schema.xml file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'table_rename',
            'db_schema.xml',
            'etc'
        );
        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule1']
        );
        $before = $this->describeTable->describeShard('default');
        $adapter = $this->resourceConnection->getConnection('default');
        $adapter->insert(
            $this->resourceConnection->getTableName('some_table'),
            $dataToMigrate
        );
        $this->isUsingAuroraDb() ?
            $this->assertStringContainsString($before['some_table'], $this->getTrimmedData()['before']) :
            $this->assertEquals($this->getData()['before'], $before['some_table']);
        //Move db_schema.xml file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'table_rename_after',
            'db_schema.xml',
            'etc'
        );

        $this->cliCommand->upgrade();
        $after = $this->describeTable->describeShard('default');
        $this->isUsingAuroraDb() ?
            $this->assertStringContainsString($after['some_table_renamed'], $this->getTrimmedData()['after']) :
            $this->assertEquals($this->getData()['after'], $after['some_table_renamed']);
        $select = $adapter->select()
            ->from($this->resourceConnection->getTableName('some_table_renamed'));
        self::assertEquals([$dataToMigrate], $adapter->fetchAll($select));
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule8
     * @throws \Exception
     */
    public function testForeignKeyReferenceId()
    {
        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule8']
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule8',
            'unpatterned_fk_name',
            'db_schema.xml',
            'etc'
        );
        $this->cliCommand->upgrade();
        $tableStatements = $this->describeTable->describeShard('default');
        $tableSql = $tableStatements['dependent'];
        $this->assertMatchesRegularExpression('/CONSTRAINT\s`DEPENDENT_PAGE_ID_ON_TEST_TABLE_PAGE_ID`/', $tableSql);
        $this->assertMatchesRegularExpression(
            '/CONSTRAINT\s`DEPENDENT_SCOPE_ID_ON_TEST_SCOPE_TABLE_SCOPE_ID`/',
            $tableSql
        );
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @moduleName Magento_TestSetupDeclarationModule8
     * @throws \Exception
     */
    public function testDisableIndexByExternalModule()
    {
        $this->cliCommand->install(
            ['Magento_TestSetupDeclarationModule1', 'Magento_TestSetupDeclarationModule8']
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'index_to_disable',
            'db_schema.xml',
            'etc'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule8',
            'disable_index_by_external_module',
            'db_schema.xml',
            'etc'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule8',
            'disable_index_by_external_module',
            'db_schema_whitelist.json',
            'etc'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule8',
            'disable_index_by_external_module',
            'module.xml',
            'etc'
        );
        $this->cliCommand->upgrade();
        $tableStatements = $this->describeTable->describeShard('default');
        $tableSql = $tableStatements['test_table'];
        $this->assertDoesNotMatchRegularExpression(
            '/KEY\s+`TEST_TABLE_VARCHAR`\s+\(`varchar`\)/',
            $tableSql,
            'Index is not being disabled by external module'
        );
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule8
     * @moduleName Magento_TestSetupDeclarationModule9
     * @dataProviderFromFile Magento/TestSetupDeclarationModule9/fixture/declarative_installer/disabling_tables.php
     * @throws \Exception
     */
    public function testInstallationWithDisablingTables()
    {
        $modules = [
            'Magento_TestSetupDeclarationModule8',
            'Magento_TestSetupDeclarationModule9',
        ];

        foreach ($modules as $moduleName) {
            $this->moduleManager->updateRevision(
                $moduleName,
                'disabling_tables',
                'db_schema.xml',
                'etc'
            );
        }
        $this->cliCommand->install($modules);

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->getAll());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        $this->assertTableCreationStatements($this->getData(), $shardData);
    }

    /**
     * Assert table creation statements
     *
     * @param array $expectedData
     * @param array $actualData
     */
    private function assertTableCreationStatements(array $expectedData, array $actualData): void
    {
        if (!$this->isUsingAuroraDb()) {
            $this->assertEquals($expectedData, $actualData);
        } else {
            ksort($expectedData);
            ksort($actualData);
            $this->assertSameSize($expectedData, $actualData);
            foreach ($expectedData as $key => $value) {
                $this->assertStringContainsString($actualData[$key], $value);
            }
        }
    }
}
