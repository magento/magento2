<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Setup\Model\Declaration\Schema\ChangeRegistryFactory;
use Magento\Setup\Model\Declaration\Schema\ChangeRegistryInterface;
use Magento\Setup\Model\Declaration\Schema\Db\Parser;
use Magento\Setup\Model\Declaration\Schema\Diff\SchemaDiff;
use Magento\Setup\Model\Declaration\Schema\Dto\SchemaFactory;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;
use \Magento\Setup\Model\Declaration\Schema\Declaration\Parser as DeclarativeParser;

/**
 * The purpose of this test is verifying initial InstallSchema, InstallData scripts
 */
class DiffOldSchemaTest extends SetupTestCase
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
     * @var SchemaFactory
     */
    private $SchemaFactory;

    /**
     * @var DeclarativeParser
     */
    private $declarativeParser;

    /**
     * @var Parser
     */
    private $generatedParser;

    /**
     * @var SchemaDiff
     */
    private $SchemaDiff;

    /**
     * @var ChangeRegistryFactory
     */
    private $changeRegistryFactory;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->SchemaFactory = $objectManager->get(SchemaFactory::class);
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommad = $objectManager->get(CliCommand::class);
        $this->declarativeParser = $objectManager->get(DeclarativeParser::class);
        $this->generatedParser = $objectManager->get(Parser::class);
        $this->SchemaDiff = $objectManager->get(SchemaDiff::class);
        $this->changeRegistryFactory = $objectManager->get(ChangeRegistryFactory::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     */
    public function testOldDiff()
    {
        //Move db_schema.xml
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'old_diff',
            'db_schema.xml',
            'etc'
        );
        //Move InstallSchema file and tried to install
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'old_diff',
            'InstallSchema.php',
            'Setup'
        );

        $this->cliCommad->install(['Magento_TestSetupDeclarationModule1']);
        $changeRegistry = $this->changeRegistryFactory->create();
        $generatedSchema = $this->SchemaFactory->create();
        $declarativeSchema = $this->SchemaFactory->create();
        $declarativeSchema = $this->declarativeParser->parse($declarativeSchema);
        $generatedSchema = $this->generatedParser->parse($generatedSchema);
        $this->SchemaDiff->diff($declarativeSchema, $generatedSchema, $changeRegistry);
        //Change operations
        $changeOperations = $changeRegistry->get(ChangeRegistryInterface::CHANGE_OPERATION);
        self::assertCount(1, $changeOperations);
        self::assertEquals(
            $this->getBigIntKeyXmlSensitiveData(),
            $changeOperations['biginteger'][0]['new']->getDiffSensitiveParams()
        );
        self::assertEquals(
            $this->getBigIntKeyDbSensitiveData(),
            $changeOperations['biginteger'][0]['old']->getDiffSensitiveParams()
        );
        self::assertCount(
            5,
            $changeRegistry->get(ChangeRegistryInterface::REMOVE_OPERATION)['table']
        );
    }

    /**
     * @return array
     */
    private function getBigIntKeyDbSensitiveData()
    {
        return [
            'type' => 'biginteger',
            'nullable' => true,
            'padding' => '20',
            'unsigned' => false,
            'identity' => false,
            'default' => 0,
        ];
    }

    /**
     * @return array
     */
    private function getBigIntKeyXmlSensitiveData()
    {
        return [
            'type' => 'biginteger',
            'nullable' => true,
            'padding' => '20',
            'unsigned' => false,
            'identity' => false,
            'default' => 1,
        ];
    }
}
