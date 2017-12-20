<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Setup\Model\Declaration\Schema\Diff\DiffFactory;
use Magento\Setup\Model\Declaration\Schema\Diff\DiffInterface;
use Magento\Setup\Model\Declaration\Schema\SchemaConfig;
use Magento\Setup\Model\Declaration\Schema\Diff\SchemaDiff;
use Magento\Setup\Model\Declaration\Schema\SchemaConfigInterface;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

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
     * @var SchemaDiff
     */
    private $schemaDiff;

    /**
     * @var DiffFactory
     */
    private $changeRegistryFactory;

    /**
     * @var SchemaConfigInterface
     */
    private $schemaConfig;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommad = $objectManager->get(CliCommand::class);
        $this->schemaConfig = $objectManager->get(SchemaConfigInterface::class);
        $this->schemaDiff = $objectManager->get(SchemaDiff::class);
        $this->changeRegistryFactory = $objectManager->get(DiffFactory::class);
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
        $declarativeSchema = $this->schemaConfig->getDeclarationConfig();
        $generatedSchema = $this->schemaConfig->getDbConfig();
        $diff = $this->schemaDiff->diff($declarativeSchema, $generatedSchema);
        //Change operations
        $changeOperations = $diff->get(DiffInterface::CHANGE_OPERATION);
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
            $diff->get(DiffInterface::REMOVE_OPERATION)['table']
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
