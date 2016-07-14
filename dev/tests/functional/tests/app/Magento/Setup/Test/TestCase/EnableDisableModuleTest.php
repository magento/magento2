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

        if (false && !$this->setupWizard->getModuleGrid()->isModuleEnabled($module['moduleName'])) {
            $this->setupWizard->getModuleGrid()->enableModule($module['moduleName']);

            // Readiness Check
            $this->setupWizard->getReadiness()->clickReadinessCheck();
            $assertReadiness->processAssert($this->setupWizard);
            $this->setupWizard->getReadiness()->clickNext();

            // Create Backup page
            $this->setupWizard->getCreateBackup()->fill($createBackupFixture);
            $this->setupWizard->getCreateBackup()->clickNext();
        }

        $this->setupWizard->getModuleGrid()->disableModule($module['moduleName']);

        // Readiness Check
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();

        // Create Backup page
        $this->setupWizard->getCreateBackup()->fill($createBackupFixture);
        $this->setupWizard->getCreateBackup()->clickNext();

        // Disable Module
        $this->setupWizard->getModuleDisable()->clickDisable();

        // Assert for Success message
        $assertSuccessMessage->processAssert($this->setupWizard);
    }
}
