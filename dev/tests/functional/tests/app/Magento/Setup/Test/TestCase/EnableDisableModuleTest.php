<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Setup\Test\Constraint\Module\AssertModuleInGrid;
use Magento\Setup\Test\Constraint\AssertSuccessfulReadinessCheck;
use Magento\Setup\Test\Constraint\Module\AssertSuccessMessage;
use Magento\Setup\Test\Fixture\BackupOptions;
use Magento\Setup\Test\Fixture\Module;
use Magento\Setup\Test\Page\Adminhtml\SetupWizard;

/**
 * Preconditions:
 * 1. Appropriate Module must be installed and enabled.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to System > Web Setup Wizard.
 * 3. Click "Module Manager" button.
 * 4. Find Module in the Grid and click Select > Disable module.
 * 5. Perform Readiness Checks.
 * 6. Perform DB Backup.
 * 7. Click "Disable" button.
 * 8. Check for Success message
 * 9. Return to "Web Setup Wizard".
 * 10. Click "Module Manager" button.
 * 11. Find appropriate Module in the Grid.
 * 12. Find Module in the Grid and click Select > Enable module.
 * 13. Perform Readiness Checks.
 * 14. Perform DB Backup.
 * 15. Click "Enable" button.
 * 16. Check for Success message
 * 17. Return to "Web Setup Wizard".
 *
 * @group Setup
 * @ZephyrId MAGETWO-43202
 */
class EnableDisableModuleTest extends Injectable
{
    /**
     * Dashboard page.
     *
     * @var Dashboard
     */
    private $adminDashboard;

    /**
     * Web Setup Wizard page.
     *
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
     * @param BackupOptions $backupOptions
     * @param AssertModuleInGrid $assertModuleInGrid
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param AssertSuccessMessage $assertSuccessMessage
     */
    public function test(
        Module $module,
        BackupOptions $backupOptions,
        AssertModuleInGrid $assertModuleInGrid,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertSuccessMessage $assertSuccessMessage
    ) {
        // Open Backend
        $this->adminDashboard->open();

        // Go to System > Web Setup Wizard
        $this->setupWizard->open();

        // Click "Module Manager" button
        $this->setupWizard->getSetupHome()->clickModuleManager();

        // Find appropriate Module in the grid
        $assertModuleInGrid->processAssert($this->setupWizard, $module->getModuleName());

        if (!$this->setupWizard->getModuleGrid()->isModuleEnabled($module->getModuleName())) {
            $this->fail('Module is already disabled.');
        }

        // Find Module in the Grid and click Select > Disable module
        $this->setupWizard->getModuleGrid()->disableModule($module->getModuleName());

        // Perform Readiness Checks
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();

        // Perform DB Backup
        $this->setupWizard->getCreateBackup()->fill($backupOptions);
        $this->setupWizard->getCreateBackup()->clickNext();

        // Click "Disable" button
        $this->setupWizard->getModuleStatus()->clickDisable();

        // Check for Success message
        $assertSuccessMessage->processAssert($this->setupWizard);

        // Return to "Web Setup Wizard"
        $this->setupWizard->getSuccessMessage()->clickBackToSetup();

        // Find appropriate Module in the Grid
        $assertModuleInGrid->processAssert($this->setupWizard, $module->getModuleName());

        // Find Module in the Grid and click Select > Enable module
        $this->setupWizard->getModuleGrid()->enableModule($module->getModuleName());

        // Perform Readiness Checks
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();

        // Perform DB Backup
        $this->setupWizard->getCreateBackup()->fill($backupOptions);
        $this->setupWizard->getCreateBackup()->clickNext();

        // Click "Enable" button
        $this->setupWizard->getModuleStatus()->clickEnable();

        // Check for Success message
        $assertSuccessMessage->processAssert($this->setupWizard);

        // Return to "Web Setup Wizard"
        $this->setupWizard->getSuccessMessage()->clickBackToSetup();
    }
}
