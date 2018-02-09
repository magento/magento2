<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup;

use Magento\Framework\Module\ModuleResource;
use Magento\Setup\Model\Patch\PatchHistory;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TableData;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\SetupTestCase;
use Magento\TestSetupDeclarationModule3\Setup\Patch\Data\ZFirstPatch;
use Magento\TestSetupDeclarationModule3\Setup\Patch\Data\IncrementalSomeIntegerPatch;
use Magento\TestSetupDeclarationModule3\Setup\Patch\Data\ReferenceIncrementalSomeIntegerPatch;

/**
 * The purpose of this test is validating schema reader operations.
 */
class DataPatchInstallationTest extends SetupTestCase
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
     * @var ModuleResource
     */
    private $moduleResource;

    /**
     * @var PatchHistory
     */
    private $patchList;

    /**
     * @var TableData
     */
    private $tableData;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->moduleManager = $objectManager->get(TestModuleManager::class);
        $this->cliCommad = $objectManager->get(CliCommand::class);
        $this->moduleResource = $objectManager->get(ModuleResource::class);
        $this->patchList = $objectManager->get(PatchHistory::class);
        $this->tableData = $objectManager->get(TableData::class);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule3
     */
    public function testDataPatchesInstallation()
    {
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule3']
        );

        self::assertEquals(
            '0.0.1',
            $this->moduleResource->getDataVersion('Magento_TestSetupDeclarationModule3')
        );

        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule3',
            'first_patch_revision',
            'module.xml',
            'etc'
        );
        $this->movePatches();
        $this->moduleResource->flush();
        $this->cliCommad->upgrade();
        self::assertEquals(
            '0.0.3',
            $this->moduleResource->getDataVersion('Magento_TestSetupDeclarationModule3')
        );
        self::assertTrue($this->patchList->isApplied(IncrementalSomeIntegerPatch::class));
        self::assertTrue($this->patchList->isApplied(ReferenceIncrementalSomeIntegerPatch::class));
        self::assertFalse($this->patchList->isApplied(ZFirstPatch::class));
        $tableData = $this->tableData->describeTableData('test_table');
        self::assertEquals($this->getTestTableData(), $tableData);
    }

    /**
     * @moduleName Magento_TestSetupDeclarationModule3
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testCyclomaticDependency()
    {
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule3',
            'cyclomatic_and_bic_revision',
            'module.xml',
            'etc'
        );

        $this->movePatches();
        /**
         * Test whether installation give the same result as upgrade
         */
        $this->cliCommad->install(
            ['Magento_TestSetupDeclarationModule3']
        );
        $tableData = $this->tableData->describeTableData('test_table');
        self::assertEquals($this->getTestTableData(), $tableData);
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule3',
            'cyclomatic_and_bic_revision',
            'BicPatch.php',
            'Setup/Patch/Data'
        );
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule3',
            'cyclomatic_and_bic_revision',
            'RefBicPatch.php',
            'Setup/Patch/Data'
        );

        $this->cliCommad->upgrade();
    }

    /**
     * Move patches
     */
    private function movePatches()
    {
        //Install with patches
        $this->moduleManager->addRevision(
            'Magento_TestSetupDeclarationModule3',
            'patches_revision',
            'Setup/Patch/Data'
        );
        //Upgrade with UpgradeData
        $this->moduleManager->updateRevision(
            'Magento_TestSetupDeclarationModule3',
            'first_patch_revision',
            'UpgradeData.php',
            'Setup'
        );
    }

    /**
     * @return array
     */
    private function getTestTableData()
    {
        return [
            [
                'smallint' => '1',
                'tinyint' => NULL,
                'varchar' => 'Ololo123',
                'varbinary' => '33288',
            ],
            [
                'smallint' => '2',
                'tinyint' => NULL,
                'varchar' => 'Ololo123_ref',
                'varbinary' => '33288',
            ],
            [
                'smallint' => '3',
                'tinyint' => NULL,
                'varchar' => 'changed__very_secret_string',
                'varbinary' => '0',
            ],
        ];
    }
}
