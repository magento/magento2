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
use Magento\Setup\Test\Fixture\BackupOptions;
use Magento\Setup\Test\Fixture\RepoCredentials;
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
    ) {
        $this->adminDashboard = $adminDashboard;
        $this->setupWizard = $setupWizard;
    }

    /**
     * Set credentials for connecting to repo.magento.com
     *
     * @param bool $needAuthentication
     * @param RepoCredentials $repoCredentials
     * @return void
     */
    protected function repoAuthentication($needAuthentication, RepoCredentials $repoCredentials)
    {
        if ($needAuthentication) {
            $this->setupWizard->getSystemConfig()->clickSystemConfig();
            $this->setupWizard->getAuthentication()->fill($repoCredentials);
            $this->setupWizard->getAuthentication()->clickSaveConfig();
            $this->setupWizard->open();
        }
    }

    /**
     * Readiness check and Create Backup steps.
     *
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param BackupOptions $backupOptions
     * @return void
     */
    protected function readinessCheckAndBackup(
        AssertSuccessfulReadinessCheck $assertReadiness,
        BackupOptions $backupOptions
    ) {
        // Readiness Check
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();

        // Create Backup page
        $this->setupWizard->getCreateBackup()->fill($backupOptions);
        $this->setupWizard->getCreateBackup()->clickNext();
    }

    /**
     * Uninstall extension.
     *
     * @param Extension $extension
     * @param BackupOptions $backupOptions
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param AssertFindExtensionOnGrid $assertFindExtensionOnGrid
     * @param AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck
     * @param AssertSuccessMessage $assertSuccessMessage
     */
    protected function uninstallExtension(
        Extension $extension,
        BackupOptions $backupOptions,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertFindExtensionOnGrid $assertFindExtensionOnGrid,
        AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck,
        AssertSuccessMessage $assertSuccessMessage
    ) {
        // Open Extension Grid with installed extensions and find installed extension
        $this->setupWizard->open();
        $this->setupWizard->getSetupHome()->clickExtensionManager();
        $this->setupWizard->getExtensionsGrid()->waitLoader();
        $assertFindExtensionOnGrid->processAssert($this->setupWizard->getExtensionsGrid(), $extension);

        // Click to uninstall extension
        $this->setupWizard->getExtensionsGrid()->clickUninstallButton($extension);

        $this->readinessCheckAndBackup($assertReadiness, $backupOptions);

        // Data Option (keep or remove data of extension)
        $this->setupWizard->getDataOption()->clickNext();

        // Uninstall extension
        $assertExtensionAndVersionCheck->processAssert(
            $this->setupWizard,
            $extension,
            AssertExtensionAndVersionCheck::TYPE_UNINSTALL
        );
        $this->setupWizard->getUpdaterExtension()->clickStartButton();
        $assertSuccessMessage->processAssert(
            $this->setupWizard,
            $extension,
            AssertSuccessMessage::TYPE_UNINSTALL
        );

        // Check that extension is uninstalled
        $this->setupWizard->open();
        $this->setupWizard->getSetupHome()->clickExtensionManager();
        $this->setupWizard->getExtensionsGrid()->waitLoader();

        if ($this->setupWizard->getExtensionsGrid()->findExtensionOnGrid($extension)) {
            $this->fail('Extension is not uninstalled!');
        }
    }
}
