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
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * The purpose of this test is verifying initial InstallSchema, InstallData scripts
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

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->schemaConfig = $objectManager->create(SchemaConfig::class);
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
    }

    /**
     * Tests primary key constraint convertion from XML and renamed functionality
     *
     * @moduleName Magento_TestSetupDeclarationModule1
     */
    public function testSchemaBuilder()
    {
        $dbSchema = $this->schemaConfig->getDeclarationConfig();
        //Test primary key and renaming
        $referenceTable = $dbSchema->getTableByName('reference_table');
        /**
 * @var Internal $primaryKey 
*/
        $primaryKey = $referenceTable->getPrimaryConstraint();
        $columns = $primaryKey->getColumns();
        self::assertEquals(reset($columns)->getName(), 'tinyint_ref');
        //Test column
        $testTable = $dbSchema->getTableByName('test_table');
        /**
 * @var Timestamp $timestampColumn 
*/
        $timestampColumn = $testTable->getColumnByName('timestamp');
        self::assertEquals($timestampColumn->getOnUpdate(), 'CURRENT_TIMESTAMP');
        //Test disabled
        self::assertArrayNotHasKey('varbinary_rename', $testTable->getColumns());
        //Test foreign key
        /**
 * @var Reference $foreignKey 
*/
        $foreignKey = $testTable->getConstraintByName('some_foreign_key');
        self::assertEquals($foreignKey->getOnDelete(), 'NO ACTION');
        self::assertEquals($foreignKey->getReferenceColumn()->getName(), 'tinyint_ref');
    }
}
