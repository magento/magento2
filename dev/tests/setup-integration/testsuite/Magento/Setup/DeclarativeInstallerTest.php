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
    private $cliCommad;

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

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommad = $objectManager->get(CliCommand::class);
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
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1']
        );

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );

        //Second time installation should not find anything as we do not change anything
        self::assertNull($diff->getAll());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        self::assertEquals($this->getTrimmedData(), $shardData);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/column_modification.php
     */
    public function testInstallationWithColumnsModification()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1']
        );

        //Move InstallSchema file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'column_modifications',
            'db_schema.xml',
            'etc'
        );

        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1']
        );

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->getAll());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        self::assertEquals($this->getTrimmedData(), $shardData);
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
     */
    public function testInstallationWithColumnsRemoval()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1']
        );
        $this->updateDbSchemaRevision('column_removals');
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1']
        );

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->getAll());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        self::assertEquals($this->getTrimmedData(), $shardData);
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
     */
    public function testInstallationWithConstraintsModification()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1']
        );
        $this->updateDbSchemaRevision('constraint_modifications');
        $this->cliCommad->upgrade();

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->getAll());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        self::assertEquals($this->getTrimmedData(), $shardData);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/table_removal.php
     */
    public function testInstallationWithDroppingTables()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1']
        );

        //Move db_schema.xml file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'drop_table',
            'db_schema.xml',
            'etc'
        );

        $this->cliCommad->upgrade();

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->getAll());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        self::assertEquals($this->getData(), $shardData);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/rollback.php
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
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1']
        );
        $beforeRollback = $this->describeTable->describeShard('default');
        self::assertEquals($this->getTrimmedData()['before'], $beforeRollback);
        //Move db_schema.xml file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'after_rollback',
            'db_schema.xml',
            'etc'
        );

        $this->cliCommad->upgrade();
        $afterRollback = $this->describeTable->describeShard('default');
        self::assertEquals($this->getData()['after'], $afterRollback);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/table_rename.php
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
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1']
        );
        $before = $this->describeTable->describeShard('default');
        $adapter = $this->resourceConnection->getConnection('default');
        $adapter->insert(
            $this->resourceConnection->getTableName('some_table'),
            $dataToMigrate
        );
        self::assertEquals($this->getData()['before'], $before['some_table']);
        //Move db_schema.xml file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'table_rename_after',
            'db_schema.xml',
            'etc'
        );

        $this->cliCommad->upgrade();
        $after = $this->describeTable->describeShard('default');
        self::assertEquals($this->getData()['after'], $after['some_table_renamed']);
        $select = $adapter->select()
            ->from($this->resourceConnection->getTableName('some_table_renamed'));
        self::assertEquals([$dataToMigrate], $adapter->fetchAll($select));
    }
}
