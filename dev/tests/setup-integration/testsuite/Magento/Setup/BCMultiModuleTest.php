<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\Module\DbVersionInfo;
use Magento\Framework\Module\ModuleResource;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaReaderInterface;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TableData;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * The purpose of this test is to check whether whole declarative installation is work
 * in mixed mode
 */
class BCMultiModuleTest extends SetupTestCase
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
     * @var DbVersionInfo
     */
    private $dbVersionInfo;

    /**
     * @var TableData
     */
    private $tableData;

    /**
     * @var ModuleResource
     */
    private $moduleResource;

    /**
     * @var DbSchemaReaderInterface
     */
    private $dbSchemaReader;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommand = $objectManager->get(CliCommand::class);
        $this->dbVersionInfo = $objectManager->get(DbVersionInfo::class);
        $this->tableData = $objectManager->get(TableData::class);
        $this->moduleResource = $objectManager->get(ModuleResource::class);
        $this->dbSchemaReader = $objectManager->get(DbSchemaReaderInterface::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule6
     * @moduleName Magento_TestSetupDeclarationModule7
     */
    public function testFirstCleanInstall()
    {
        $this->cliCommand->install([
            'Magento_TestSetupDeclarationModule6',
            'Magento_TestSetupDeclarationModule7'
        ]);
        //Check if declaration is applied
        $indexes = $this->dbSchemaReader->readIndexes('test_table', 'default');
        self::assertCount(1, $indexes);
        self::assertArrayHasKey('TEST_TABLE_TINYINT_BIGINT', $indexes);
        //Check UpgradeSchema old format, that modify declaration
        $columns = $this->dbSchemaReader->readColumns('test_table', 'default');
        $floatColumn = $columns['float'];
        self::assertEquals(29, $floatColumn['default']);
    }

    private function doUsToUsRevision()
    {
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule7',
            'us_to_us',
            'UpgradeSchema.php',
            'Setup'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule7',
            'us_to_us',
            'module.xml',
            'etc'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule7',
            'us_to_us',
            'UpgradeData.php',
            'Setup'
        );
    }

    private function doUsToDsRevision()
    {
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule7',
            'swap_with_declaration',
            'db_schema.xml',
            'etc'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule7',
            'swap_with_declaration',
            'SomePatch.php',
            'Setup/Patch/Data'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule7',
            'swap_with_declaration',
            'SomeSkippedPatch.php',
            'Setup/Patch/Data'
        );
    }

    /**
     * Assert that data and schema of 2 modules are installed successfully
     */
    private function assertUsToUsUpgrade()
    {
        $usToUsTables = $this->dbSchemaReader->readTables('default');
        self::assertContains('custom_table', $usToUsTables);
        self::assertTrue($this->dbVersionInfo->isDataUpToDate('Magento_TestSetupDeclarationModule7'));
        self::assertTrue($this->dbVersionInfo->isSchemaUpToDate('Magento_TestSetupDeclarationModule7'));
        self::assertEquals(
            [6,12],
            $this->tableData->describeTableData('reference_table', 'bigint_without_padding')
        );
    }

    /**
     * Assert that data and schema of 2 modules are installed successfully
     */
    private function assertUsToDsUpgrade()
    {
        //Check UpgradeSchema old format, that modify declaration
        $columns = $this->dbSchemaReader->readColumns('test_table', 'default');
        $floatColumn = $columns['float'];
        //Check whether declaration will be applied
        self::assertEquals(35, $floatColumn['default']);
        self::assertTrue($this->dbVersionInfo->isDataUpToDate('Magento_TestSetupDeclarationModule7'));
        self::assertTrue($this->dbVersionInfo->isSchemaUpToDate('Magento_TestSetupDeclarationModule7'));
        self::assertEquals(
            [6,12],
            $this->tableData->describeTableData('reference_table', 'bigint_without_padding')
        );
        self::assertEquals(
            ['_ref'],
            $this->tableData->describeTableData('test_table', 'varchar')
        );
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule6
     * @moduleName Magento_TestSetupDeclarationModule7
     */
    public function testDSFirstRelease()
    {
        $this->cliCommand->install([
            'Magento_TestSetupDeclarationModule6',
            'Magento_TestSetupDeclarationModule7'
        ]);
        //Check no change upgrade with US
        $this->cliCommand->upgrade();

        $this->doUsToUsRevision();
        //Check US to US upgrade
        $this->cliCommand->upgrade();
        $this->assertUsToUsUpgrade();

        $this->doUsToDsRevision();
        //Check US to declarative schema upgrade
        $this->cliCommand->upgrade();
        $this->assertUsToDsUpgrade();

        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule7',
            'wl_remove_table',
            'db_schema_whitelist.json',
            'etc'
        );
        //Check removal case, when we need to remove table with declaration and table was created in old scripts
        $this->cliCommand->upgrade();
        $tables = $this->dbSchemaReader->readTables('default');
        self::assertNotContains('custom_table', $tables);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @dataProvider firstCleanInstallOneModuleDataProvider
     * @param string $dbPrefix
     * @param string $tableName
     * @param string $indexName
     * @param string $constraintName
     * @param string $foreignKeyName
     * @throws \Exception
     */
    public function testFirstCleanInstallOneModule(
        string $dbPrefix,
        string $tableName,
        string $indexName,
        string $constraintName,
        string $foreignKeyName
    ) {
        $this->cliCommand->install(
            [
                'Magento_TestSetupDeclarationModule1'
            ],
            [
                'db-prefix' => $dbPrefix,
            ]
        );

        $indexes = $this->dbSchemaReader
            ->readIndexes($tableName, 'default');
        self::assertCount(1, $indexes);
        self::assertArrayHasKey($indexName, $indexes);

        $constraints = $this->dbSchemaReader
            ->readConstraints($tableName, 'default');
        self::assertCount(1, $constraints);
        self::assertArrayHasKey($constraintName, $constraints);

        $foreignKeys = $this->dbSchemaReader
            ->readReferences($tableName, 'default');
        self::assertCount(1, $foreignKeys);
        self::assertArrayHasKey($foreignKeyName, $foreignKeys);
    }

    /**
     * @return array
     */
    public function firstCleanInstallOneModuleDataProvider()
    {
        return [
            'Installation without db prefix' => [
                'dbPrefix' => '',
                'tableName' => 'test_table',
                'indexName' => 'TEST_TABLE_TINYINT_BIGINT',
                'constraintName' => 'TEST_TABLE_SMALLINT_BIGINT',
                'foreignKeyName' => 'TEST_TABLE_TINYINT_REFERENCE_TABLE_TINYINT_REF',
            ],
            'Installation with db prefix' => [
                'dbPrefix' => 'spec_',
                'tableName' => 'spec_test_table',
                'indexName' => 'SPEC_TEST_TABLE_TINYINT_BIGINT',
                'constraintName' => 'SPEC_TEST_TABLE_SMALLINT_BIGINT',
                'foreignKeyName' => 'SPEC_TEST_TABLE_TINYINT_SPEC_REFERENCE_TABLE_TINYINT_REF',
            ]
        ];
    }
}
