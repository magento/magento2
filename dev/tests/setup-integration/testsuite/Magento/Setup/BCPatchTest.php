<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\Module\DbVersionInfo;
use Magento\Framework\Module\ModuleList;
use Magento\Framework\Module\ModuleResource;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TableData;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;

/**
 * The purpose of this test is to check whether patch changes in Magento are backward compatible
 */
class BCPatchTest extends SetupTestCase
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

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommand = $objectManager->get(CliCommand::class);
        $this->dbVersionInfo = $objectManager->get(DbVersionInfo::class);
        $this->tableData = $objectManager->get(TableData::class);
        $this->moduleResource = $objectManager->get(ModuleResource::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule5
     */
    public function testSuccessfullInstall()
    {
        $this->cliCommand->install(['Magento_TestSetupDeclarationModule5']);
        self::assertTrue($this->dbVersionInfo->isDataUpToDate('Magento_TestSetupDeclarationModule5'));
        self::assertTrue($this->dbVersionInfo->isSchemaUpToDate('Magento_TestSetupDeclarationModule5'));
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule5
     */
    public function testDataMixedMode()
    {
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule5',
            'old-scripts',
            'UpgradeData.php',
            'Setup'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule5',
            'patches',
            'SomePatch.php',
            'Setup/Patch/Data'
        );
        
        $this->cliCommand->install(['Magento_TestSetupDeclarationModule5']);
        self::assertTrue($this->dbVersionInfo->isDataUpToDate('Magento_TestSetupDeclarationModule5'));
        self::assertTrue($this->dbVersionInfo->isSchemaUpToDate('Magento_TestSetupDeclarationModule5'));
        self::assertEquals(
            [6,12],
            $this->tableData->describeTableData('reference_table', 'some_integer')
        );
        self::assertEquals(
            ['_ref'],
            $this->tableData->describeTableData('test_table', 'varchar')
        );
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule5
     */
    public function testSkippedPatch()
    {
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule5',
            'patches',
            'SomePatch.php',
            'Setup/Patch/Data'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule5',
            'patches',
            'SomeSkippedPatch.php',
            'Setup/Patch/Data'
        );
        $this->cliCommand->install(['Magento_TestSetupDeclarationModule5']);
        self::assertTrue($this->dbVersionInfo->isDataUpToDate('Magento_TestSetupDeclarationModule5'));
        self::assertTrue($this->dbVersionInfo->isSchemaUpToDate('Magento_TestSetupDeclarationModule5'));
        self::assertEquals(
            ['_ref'],
            $this->tableData->describeTableData('test_table', 'varchar')
        );
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule5
     */
    public function testDataInstallationWithoutVersion()
    {
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule5',
            'module-without-version',
            'module.xml',
            'etc'
        );
        $this->cliCommand->install(['Magento_TestSetupDeclarationModule5']);
        self::assertTrue($this->dbVersionInfo->isDataUpToDate('Magento_TestSetupDeclarationModule5'));
        self::assertTrue($this->dbVersionInfo->isSchemaUpToDate('Magento_TestSetupDeclarationModule5'));
        $this->moduleResource->setDataVersion('Magento_TestSetupDeclarationModule5', '1.0.2');
        $this->moduleResource->setDataVersion('Magento_TestSetupDeclarationModule5', '1.0.2');
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule5',
            'old-scripts',
            'UpgradeData.php',
            'Setup'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule5',
            'patches',
            'SomePatch.php',
            'Setup/Patch/Data'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule5',
            'patches',
            'SomeSkippedPatch.php',
            'Setup/Patch/Data'
        );
        $this->cliCommand->upgrade();
        self::assertTrue($this->dbVersionInfo->isDataUpToDate('Magento_TestSetupDeclarationModule5'));
        self::assertTrue($this->dbVersionInfo->isSchemaUpToDate('Magento_TestSetupDeclarationModule5'));
        //Old scripts should be skipped because we do not have version
        self::assertEquals(
            [],
            $this->tableData->describeTableData('reference_table', 'some_integer')
        );
        self::assertEquals(
            ['_ref'],
            $this->tableData->describeTableData('test_table', 'varchar')
        );
    }
}
