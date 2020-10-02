<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\Setup\Declaration\Schema\Diff\DiffFactory;
use Magento\Framework\Setup\Declaration\Schema\Diff\SchemaDiff;
use Magento\Framework\Setup\Declaration\Schema\SchemaConfigInterface;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * The purpose of this test is verifying initial InstallSchema, InstallData scripts.
 */
class DiffOldSchemaTest extends SetupTestCase
{
    /**
     * @var TestModuleManager
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

    protected function setUp(): void
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
            'old_diff_before',
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
        //Move db_schema.xml
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'old_diff',
            'db_schema.xml',
            'etc'
        );
        $declarativeSchema = $this->schemaConfig->getDeclarationConfig();
        $generatedSchema = $this->schemaConfig->getDbConfig();
        $diff = $this->schemaDiff->diff($declarativeSchema, $generatedSchema);
        $allChanges = $diff->getAll();
        self::assertCount(1, $allChanges);
        self::assertEquals(
            $this->getBigIntKeyXmlSensitiveData(),
            reset($allChanges)['modify_column'][0]->getNew()->getDiffSensitiveParams()
        );
        self::assertEquals(
            $this->getBigIntKeyDbSensitiveData(),
            reset($allChanges)['modify_column'][0]->getOld()->getDiffSensitiveParams()
        );
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule1
     * @param string $dbPrefix
     * @throws \Exception
     * @dataProvider oldSchemaUpgradeDataProvider
     */
    public function testOldSchemaUpgrade(string $dbPrefix)
    {
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'old_diff_before',
            'db_schema.xml',
            'etc'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'base_update',
            'InstallSchema.php',
            'Setup'
        );
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule1'],
            ['db-prefix' => $dbPrefix]
        );
        //Move db_schema.xml
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule1',
            'base_update',
            'db_schema.xml',
            'etc'
        );
        $declarativeSchema = $this->schemaConfig->getDeclarationConfig();
        $generatedSchema = $this->schemaConfig->getDbConfig();
        $diff = $this->schemaDiff->diff($declarativeSchema, $generatedSchema);
        self::assertEmpty($diff->getAll());
    }

    /**
     * @return array
     */
    public function oldSchemaUpgradeDataProvider(): array
    {
        return [
            'Without db prefix' => [
                'dbPrefix' => '',
            ],
            'With db prefix' => [
                'dbPrefix' => 'spec_',
            ],
        ];
    }

    /**
     * @return array
     */
    private function getBigIntKeyDbSensitiveData()
    {
        return [
            'type' => 'bigint',
            'nullable' => true,
            'padding' => null,
            'unsigned' => false,
            'identity' => false,
            'default' => 0,
            'comment' => 'Bigint'
        ];
    }

    /**
     * @return array
     */
    private function getBigIntKeyXmlSensitiveData()
    {
        return [
            'type' => 'bigint',
            'nullable' => true,
            'padding' => null,
            'unsigned' => false,
            'identity' => false,
            'default' => 1,
            'comment' => 'Bigint',
        ];
    }
}
