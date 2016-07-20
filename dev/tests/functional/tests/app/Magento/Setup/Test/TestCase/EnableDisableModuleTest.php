<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Setup\Test\Constraint\Module\AssertModuleInGrid;
use Magento\Setup\Test\Constraint\AssertSuccessfulReadinessCheck;
use Magento\Setup\Test\Constraint\Module\AssertSuccessMessage;
use Magento\Setup\Test\Fixture\BackupConfig;
use Magento\Setup\Test\Fixture\Module;
use Magento\Setup\Test\Page\Adminhtml\SetupWizard;

/**
 * Preconditions:
 * 1. Appropriate Module must be installed and enabled.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to System > Web Setup Wizard.
 * 3. Click "Component Manager" button.
 * 4. Find appropriate Module in the Grid.
 * 5. Click Select > Disable module.
 * 6. Perform Readiness Checks.
 * 7. Perform DB Backup.
 * 8. Disable Module.
 * 9. Return to "Web Setup Wizard".
 * 10. Click "Component Manager" button.
 * 11. Find appropriate Module in the Grid.
 * 12. Click Select > Enable module.
 * 13. Perform Readiness Checks.
 * 14. Perform DB Backup.
 * 15. Enable Module.
 *
 * @group Setup_(CS)
 * @ZephyrId MAGETWO-43202
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
     * Injection of pages.
     *
     * @param Dashboard $dashboard
     * @param SetupWizard $setupWizard
     */
    public function __inject(Dashboard $dashboard, SetupWizard $setupWizard)
    {
        $this->adminDashboard = $dashboard;
        $this->setupWizard = $setupWizard;
    }

    /**
     * Test root method.
     *
     * @param Module $module
     * @param BackupConfig $backupConfig
     * @param AssertModuleInGrid $assertModuleInGrid
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param AssertSuccessMessage $assertSuccessMessage
     */
    public function test(
        Module $module,
        BackupConfig $backupConfig,
        AssertModuleInGrid $assertModuleInGrid,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertSuccessMessage $assertSuccessMessage
    ) {
        // Authenticate in admin area
        $this->adminDashboard->open();

        // Open Web Setup Wizard
        $this->setupWizard->open();

        // Open Modules page
        $this->setupWizard->getModuleManagement()->clickModules();

        // Search for module
        $assertModuleInGrid->processAssert($this->setupWizard, $module->getModuleName());

        if (!$this->setupWizard->getModuleGrid()->isModuleEnabled($module->getModuleName())) {
            $this->fail('Module is already disabled.');
        }

        // Find and disable Module in the Grid.
        $this->setupWizard->getModuleGrid()->disableModule($module->getModuleName());

        // Readiness Check
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();

        // Create Backup page
        $this->setupWizard->getCreateBackup()->fill($backupConfig);
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
        $assertModuleInGrid->processAssert($this->setupWizard, $module->getModuleName());

        // Find and enable Module in the Grid.
        $this->setupWizard->getModuleGrid()->enableModule($module->getModuleName());

        // Readiness Check
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();

        // Create Backup page
        $this->setupWizard->getCreateBackup()->fill($backupConfig);
        $this->setupWizard->getCreateBackup()->clickNext();

        // Enable Module
        $this->setupWizard->getModuleStatus()->clickEnable();

        // Assert for Success message
        $assertSuccessMessage->processAssert($this->setupWizard);

        // Return to Setup Tool
        $this->setupWizard->getSuccessMessage()->clickBackToSetup();
    }
}
