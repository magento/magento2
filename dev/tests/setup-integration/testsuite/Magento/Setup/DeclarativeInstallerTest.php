<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Console\Command\InstallCommand;
use Magento\Setup\Model\Declaration\Schema\Db\AdapterMediator;
use Magento\Setup\Model\Declaration\Schema\Diff\SchemaDiff;
use Magento\Setup\Model\Declaration\Schema\SchemaConfigInterface;
use Magento\Setup\Model\Declaration\Schema\Sharding;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\DescribeTable;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * The purpose of this test is verifying declarative installation works
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
            ['Magento_TestSetupDeclarationModule1'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );

        //$tablesData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        //Second time installation should not find anything as we do not change anything
        self::assertNull($diff->get());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        self::assertEquals($shardData, $this->getData());
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/column_modification.php
     */
    public function testInstallationWithColumnsModification()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        //Move InstallSchema file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'column_modifications',
            'db_schema.xml',
            'etc'
        );
        //@TODO: change this to upgrade in future
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->get());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        self::assertEquals($shardData, $this->getData());
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/column_removal.php
     */
    public function testInstallationWithColumnsRemoval()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        //Move InstallSchema file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'column_removals',
            'db_schema.xml',
            'etc'
        );

        //@TODO: change this to upgrade in future
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->get());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        self::assertEquals($shardData, $this->getData());
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
            ['Magento_TestSetupDeclarationModule1'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        //Move InstallSchema file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'constraint_modifications',
            'db_schema.xml',
            'etc'
        );

        //@TODO: change this to upgrade in future
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->get());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        self::assertEquals($shardData, $this->getTrimmedData());
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProviderFromFile Magento/TestSetupDeclarationModule1/fixture/declarative_installer/table_removal.php
     */
    public function testInstallationWithDroppingTables()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        //Move InstallSchema file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'drop_table',
            'db_schema.xml',
            'etc'
        );

        //@TODO: change this to upgrade in future
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1'],
            [InstallCommand::DECLARATION_MODE_KEY => true]
        );

        $diff = $this->schemaDiff->diff(
            $this->schemaConfig->getDeclarationConfig(),
            $this->schemaConfig->getDbConfig()
        );
        self::assertNull($diff->get());
        $shardData = $this->describeTable->describeShard(Sharding::DEFAULT_CONNECTION);
        self::assertEquals($shardData, $this->getData());
    }
}
