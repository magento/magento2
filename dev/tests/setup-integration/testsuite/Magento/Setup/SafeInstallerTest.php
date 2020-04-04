<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaReaderInterface;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * The purpose of this test is verifying safe declarative installation works.
 */
class SafeInstallerTest extends SetupTestCase
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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DbSchemaReaderInterface
     */
    private $dbSchemaReader;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommand = $objectManager->get(CliCommand::class);
        $this->resourceConnection = $objectManager->get(ResourceConnection::class);
        $this->dbSchemaReader = $objectManager->get(DbSchemaReaderInterface::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule4
     * @dataProviderFromFile Magento/TestSetupDeclarationModule4/fixture/safe_data_provider.php
     */
    public function testInstallation()
    {
        $testTableData = $this->getData();
        $row = reset($testTableData);
        $this->cliCommand->install(['Magento_TestSetupDeclarationModule4']);
        $adapter = $this->resourceConnection->getConnection();
        $testTableName = $this->resourceConnection->getTableName('test_table');
        $adapter->insertArray(
            $this->resourceConnection->getTableName('test_table'),
            array_keys($row),
            $this->getData()
        );
        //Move new db_schema.xml
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule4',
            'remove_title_column',
            'db_schema.xml',
            'etc'
        );
        $this->cliCommand->upgrade(
            [
                'safe-mode' => true,
            ]
        );
        //Move new db_schema.xml with restored title field
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule4',
            'restore_title_column',
            'db_schema.xml',
            'etc'
        );
        $this->cliCommand->upgrade(
            [
                'data-restore' => true,
            ]
        );
        $testTableSelect = $adapter->select()->from($testTableName);
        self::assertEquals($testTableData, $adapter->fetchAll($testTableSelect));
    }

    /**
     * Tests that not whitelisted elements should not be removed from DB to avoid backwards-incompatible change.
     *
     * @moduleName Magento_TestSetupDeclarationModule6
     */
    public function testDestructiveOperationBehaviour()
    {
        $this->cliCommand->install(['Magento_TestSetupDeclarationModule6']);
        $this->assertForeignKeyPresence('test_table', 'TEST_TABLE_TINYINT_REFERENCE_TABLE_TINYINT_REF');

        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule6',
            'remove_fk_declaration',
            'db_schema.xml',
            'etc'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule6',
            'remove_fk_declaration',
            'db_schema_whitelist.json',
            'etc'
        );
        $this->cliCommand->upgrade();
        $this->assertForeignKeyPresence('test_table', 'TEST_TABLE_TINYINT_REFERENCE_TABLE_TINYINT_REF');

        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule6',
            'restore_fk_declaration_to_wl',
            'db_schema_whitelist.json',
            'etc'
        );
        $this->cliCommand->upgrade();
        $this->assertForeignKeyPresence('test_table', 'TEST_TABLE_TINYINT_REFERENCE_TABLE_TINYINT_REF', false);
    }

    /**
     * Asserts foreign key presence.
     *
     * @param string $tableName
     * @param string $foreignKeyName
     * @param bool $isPresent
     * @return void
     */
    private function assertForeignKeyPresence(string $tableName, string $foreignKeyName, bool $isPresent = true): void
    {
        $foreignKeys = $this->dbSchemaReader
            ->readReferences($this->resourceConnection->getTableName($tableName), 'default');
        if ($isPresent) {
            $this->assertArrayHasKey($foreignKeyName, $foreignKeys);
        } else {
            $this->assertArrayNotHasKey($foreignKeyName, $foreignKeys);
        }
    }
}
