<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Setup\Model\Declaration\Schema\Declaration\Parser;
use Magento\Setup\Model\Declaration\Schema\Dto\Columns\Timestamp;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Internal;
use Magento\Setup\Model\Declaration\Schema\Dto\Constraints\Reference;
use Magento\Setup\Model\Declaration\Schema\Dto\StructureFactory;
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
     * @var Parser
     */
    private $parser;

    /**
     * @var StructureFactory
     */
    private $structureFactory;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->parser = $objectManager->create(Parser::class);
        $this->structureFactory = $objectManager->get(StructureFactory::class);
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
    }

    /**
     * Tests primary key constraint convertion from XML and renamed functionality
     *
     * @moduleName Magento_TestSetupDeclarationModule1
     */
    public function testSchemaBuilder()
    {
        $structure = $this->structureFactory->create();
        $structure = $this->parser->parse($structure);
        //Test primary key and renaming
        $referenceTable = $structure->getTableByName('reference_table');
        /** @var Internal $primaryKey */
        $primaryKey = $referenceTable->getPrimaryConstraint();
        $columns = $primaryKey->getColumns();
        self::assertEquals(reset($columns)->getName(), 'tinyintref_2');
        //Test column
        $testTable = $structure->getTableByName('test_table');
        /** @var Timestamp $timestampColumn */
        $timestampColumn = $testTable->getColumnByNameOrId('timestamp');
        self::assertEquals($timestampColumn->getOnUpdate(), 'CURRENT_TIMESTAMP');
        //Test disabled
        self::assertArrayNotHasKey('varbinary_rename', $testTable->getColumns());
        //Test foreign key
        /** @var Reference $foreignKey */
        $foreignKey = $testTable->getConstraintByName('some_foreign_key');
        self::assertEquals($foreignKey->getOnDelete(), 'NO ACTION');
        self::assertEquals($foreignKey->getReferenceColumn()->getName(), 'tinyintref_2');
    }
}
