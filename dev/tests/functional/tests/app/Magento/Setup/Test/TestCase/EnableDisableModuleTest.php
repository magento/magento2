<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Setup\Test\Constraint\AssertModule;
use Magento\Setup\Test\Constraint\AssertSuccessfulReadinessCheck;
use Magento\Setup\Test\Constraint\Module\AssertSuccessMessage;
use Magento\Setup\Test\Fixture\EnableDisableModule;
use Magento\Setup\Test\Page\Adminhtml\SetupWizard;

/**
 * Class EnableDisableModuleTest
 */
class EnableDisableModuleTest extends Injectable
{
    /**
     * @var Dashboard
     */
    private $adminDashboard;

    /**
     * @var SetupWizard
     */
    private $setupWizard;

    /**
     * @param Dashboard $dashboard
     * @param SetupWizard $setupWizard
     */
    public function __inject(Dashboard $dashboard, SetupWizard $setupWizard)
    {
        $this->adminDashboard = $dashboard;
        $this->setupWizard = $setupWizard;
    }

    /**
     * Test method.
     *
     * @param FixtureFactory $fixtureFactory
     * @param AssertModule $assertModule
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param AssertSuccessMessage $assertSuccessMessage
     * @param array $module
     */
    public function test(
        FixtureFactory $fixtureFactory,
        AssertModule $assertModule,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertSuccessMessage $assertSuccessMessage,
        array $module
    ) {
        $createBackupConfig = array_intersect_key(
            $module,
            ['optionsCode' => '', 'optionsMedia' => '', 'optionsDb' => '']
        );
        $createBackupFixture = $fixtureFactory->create(
            EnableDisableModule::class,
            ['data' => $createBackupConfig]
        );

        // Authenticate in admin area
        $this->adminDashboard->open();

        // Open Web Setup Wizard
        $this->setupWizard->open();

        // Open Modules page
        $this->setupWizard->getModuleManagement()->clickModules();

        // Search for module
        $assertModule->processAssert($this->setupWizard, $module['moduleName']);

        if (!$this->setupWizard->getModuleGrid()->isModuleEnabled($module['moduleName'])) {
            $this->fail('Module is already disabled.');
        }

        // Find and disable Module in the Grid.
        $this->setupWizard->getModuleGrid()->disableModule($module['moduleName']);

        // Readiness Check
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();

        // Create Backup page
        $this->setupWizard->getCreateBackup()->fill($createBackupFixture);
        $this->setupWizard->getCreateBackup()->clickNext();

        // Disable Module
        $this->setupWizard->getModuleStatus()->clickDisable();

        // Assert for Success message
        $assertSuccessMessage->processAssert($this->setupWizard);

        // Return to Setup Tool
        $this->setupWizard->getSuccessMessage()->clickBackToSetup();

        // Open Modules page
        $this->setupWizard->getModuleManagement()->clickModules();

        // Search for Module
        $assertModule->processAssert($this->setupWizard, $module['moduleName']);

        // Enable Module
        $this->enableModule($createBackupFixture, $assertReadiness, $assertSuccessMessage, $module);
    }

    /**
     * Enabling Module.
     *
     * @param mixed $createBackupFixture
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param AssertSuccessMessage $assertSuccessMessage
     * @param array $module
     */
    private function enableModule(
        $createBackupFixture,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertSuccessMessage $assertSuccessMessage,
        array $module
    ) {
        // Find and enable Module in the Grid.
        $this->setupWizard->getModuleGrid()->enableModule($module['moduleName']);

        // Readiness Check
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();

        // Create Backup page
        $this->setupWizard->getCreateBackup()->fill($createBackupFixture);
        $this->setupWizard->getCreateBackup()->clickNext();

        // Enable Module
        $this->setupWizard->getModuleStatus()->clickEnable();

        // Assert for Success message
        $assertSuccessMessage->processAssert($this->setupWizard);

        // Return to Setup Tool
        $this->setupWizard->getSuccessMessage()->clickBackToSetup();
    }
}
