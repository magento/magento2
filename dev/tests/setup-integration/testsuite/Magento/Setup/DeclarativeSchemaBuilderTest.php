<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Internal;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Setup\Model\Declaration\Schema\SchemaConfig;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * @magentoAppIsolation enabled
 * The purpose of this test is verifying initial InstallSchema, InstallData scripts.
 */
class DeclarativeSchemaBuilderTest extends SetupTestCase
{
    /**
     * @var  TestModuleManager
     */
    private $moduleManager;

    /**
     * @var SchemaConfig
     */
    private $schemaConfig;

    /**
     * @var CliCommand
     */
    private $cliCommad;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->schemaConfig = $objectManager->create(SchemaConfig::class);
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommad = $objectManager->get(CliCommand::class);
    }

    /**
     * Tests primary key constraint conversion from XML and renamed functionality.
     *
     * @moduleName Magento_TestSetupDeclarationModule1
     */
    public function testSchemaBuilder()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1']
        );
        $dbSchema = $this->schemaConfig->getDeclarationConfig();
        $schemaTables = $dbSchema->getTables();
        self::assertArrayHasKey('reference_table', $dbSchema->getTables());
        self::assertArrayHasKey('test_table', $dbSchema->getTables());
        //Test primary key and renaming
        $referenceTable = $schemaTables['reference_table'];
        /**
         * @var Internal $primaryKey
         */
        $primaryKey = $referenceTable->getPrimaryConstraint();
        $columns = $primaryKey->getColumns();
        self::assertEquals('tinyint_ref', reset($columns)->getName());
        //Test column
        $testTable = $schemaTables['test_table'];
        /**
         * @var Timestamp $timestampColumn
         */
        $timestampColumn = $testTable->getColumnByName('timestamp');
        self::assertEquals('CURRENT_TIMESTAMP', $timestampColumn->getOnUpdate());
        //Test disabled
        self::assertArrayNotHasKey('varbinary_rename', $testTable->getColumns());
        //Test foreign key
        /**
         * @var Reference $foreignKey
         */
        $foreignKey = $testTable->getConstraintByName('some_foreign_key');
        self::assertEquals('NO ACTION', $foreignKey->getOnDelete());
        self::assertEquals('tinyint_ref', $foreignKey->getReferenceColumn()->getName());
    }

    /**
     * @expectedException \Magento\Setup\Exception
     * @expectedExceptionMessageRegExp /Primary key can`t be applied on table "test_table". All columns should be not nullable/
     * @moduleName Magento_TestSetupDeclarationModule8
     */
    public function testFailOnInvalidPrimaryKey()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule8']
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule8',
            'invalid_primary_key',
            'db_schema.xml',
            'etc'
        );

        $this->schemaConfig->getDeclarationConfig();
    }

    /**
     * @expectedException \Magento\Setup\Exception
     * @expectedExceptionMessageRegExp /Column definition "page_id_on" and reference column definition "page_id" are different in tables "dependent" and "test_table"/
     * @moduleName Magento_TestSetupDeclarationModule8
     */
    public function testFailOnIncosistentReferenceDefinition()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule8']
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule8',
            'incosistence_reference_definition',
            'db_schema.xml',
            'etc'
        );
        $this->schemaConfig->getDeclarationConfig();
    }

    /**
     * @expectedException \Magento\Setup\Exception
     * @expectedExceptionMessageRegExp /Auto Increment column do not have index. Column - "page_id"/
     * @moduleName Magento_TestSetupDeclarationModule8
     */
    public function testFailOnInvalidAutoIncrementFiel()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule8']
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule8',
            'invalid_auto_increment',
            'db_schema.xml',
            'etc'
        );
        $this->schemaConfig->getDeclarationConfig();
    }
}
