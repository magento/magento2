<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Setup\Test\Page\Adminhtml\SetupWizard;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Setup\Test\Fixture\Extension;
use Magento\Setup\Test\Constraint\Extension\AssertFindExtensionOnGrid;
use Magento\Setup\Test\Constraint\Extension\AssertSuccessMessage;
use Magento\Setup\Test\Constraint\Extension\AssertExtensionAndVersionCheck;
use Magento\Setup\Test\Constraint\AssertSuccessfulReadinessCheck;

/**
 * AbstractExtensionTest for testing of extension manager.
 */
abstract class AbstractExtensionTest extends Injectable
{
    /**
     * Page System Upgrade Index.
     *
     * @var SetupWizard
     */
    protected $setupWizard;

    /**
     * Admin Dashboard
     *
     * @var Dashboard
     */
    protected $adminDashboard;

    /**
     * Injection data.
     *
     * @param Dashboard $adminDashboard
     * @param SetupWizard $setupWizard
     * @return void
     */
    public function __inject(
        Dashboard $adminDashboard,
        SetupWizard $setupWizard
    )
    {
        $this->adminDashboard = $adminDashboard;
        $this->setupWizard = $setupWizard;
    }

    /**
     * Readiness check and Create Backup steps.
     *
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param Extension $extensionFixture
     * @return void
     */
    protected function readinessCheckAndBackup(
        AssertSuccessfulReadinessCheck $assertReadiness,
        Extension $extensionFixture
    ) {
        // Readiness Check
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();

        // Create Backup page
        $this->setupWizard->getCreateBackup()->fill($extensionFixture);
        $this->setupWizard->getCreateBackup()->clickNext();
    }

    /**
     * Uninstall extension.
     *
     * @param Extension $extensionFixture
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param AssertFindExtensionOnGrid $assertFindExtensionOnGrid
     * @param AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck
     * @param AssertSuccessMessage $assertSuccessMessage
     * @throws \Exception
     */
    protected function uninstallExtension(
        Extension $extensionFixture,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertFindExtensionOnGrid $assertFindExtensionOnGrid,
        AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck,
        AssertSuccessMessage $assertSuccessMessage
    ) {
        // Open Extension Grid with installed extensions and find installed extension
        $this->setupWizard->open();
        $this->setupWizard->getSetupHome()->clickExtensionManager();
        $assertFindExtensionOnGrid->processAssert($this->setupWizard->getExtensionsGrid(), $extensionFixture);

        // Click to uninstall extension
        $this->setupWizard->getExtensionsGrid()->clickUninstallButton($extensionFixture);

        $this->readinessCheckAndBackup($assertReadiness, $extensionFixture);

        // Data Option (keep or remove data of extension)
        $this->setupWizard->getDataOption()->clickNext();

        // Uninstall extension
        $assertExtensionAndVersionCheck->processAssert(
            $this->setupWizard,
            $extensionFixture,
            AssertExtensionAndVersionCheck::TYPE_UNINSTALL
        );
        $this->setupWizard->getUpdaterExtension()->clickStartButton();
        $assertSuccessMessage->processAssert(
            $this->setupWizard,
            $extensionFixture,
            AssertSuccessMessage::TYPE_UNINSTALL
        );

        // Check that extension is uninstalled
        $this->setupWizard->open();
        $this->setupWizard->getSetupHome()->clickExtensionManager();

        if ($this->setupWizard->getExtensionsGrid()->findExtensionOnGrid($extensionFixture)) {
            throw new \Exception('Extension is not uninstalled!');
        }
    }
}
