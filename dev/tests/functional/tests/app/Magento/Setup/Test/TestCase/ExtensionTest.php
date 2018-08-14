<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\TestCase;

use Magento\Setup\Test\Fixture\Extension;
use Magento\Setup\Test\Fixture\BackupOptions;
use Magento\Setup\Test\Fixture\RepoCredentials;
use Magento\Setup\Test\Constraint\Extension\AssertFindExtensionOnGrid;
use Magento\Setup\Test\Constraint\Extension\AssertSuccessMessage;
use Magento\Setup\Test\Constraint\Extension\AssertExtensionAndVersionCheck;
use Magento\Setup\Test\Constraint\Extension\AssertVersionOnGrid;
use Magento\Setup\Test\Constraint\AssertSuccessfulReadinessCheck;

/**
 * ExtensionTest checks installing, updating and uninstalling of extensions.
 */
class ExtensionTest extends AbstractExtensionTest
{
    /**
     * @param AssertFindExtensionOnGrid $assertFindExtensionOnGrid
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck
     * @param AssertSuccessMessage $assertSuccessMessage
     * @param AssertVersionOnGrid $assertVersionOnGrid
     * @param bool $needAuthentication
     * @param Extension $extension
     * @param RepoCredentials $repoCredentials
     * @param BackupOptions $backupOptions
     * @return void
     */
    public function test(
        AssertFindExtensionOnGrid $assertFindExtensionOnGrid,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck,
        AssertSuccessMessage $assertSuccessMessage,
        AssertVersionOnGrid $assertVersionOnGrid,
        $needAuthentication,
        Extension $extension,
        RepoCredentials $repoCredentials,
        BackupOptions $backupOptions
    ) {
        // Authenticate in admin area
        $this->adminDashboard->open();

        // Open Web Setup Wizard
        $this->setupWizard->open();

        // Authenticate on repo.magento.com
        $this->repoAuthentication($needAuthentication, $repoCredentials);

        // Open Extension Grid with extensions to install
        $this->setupWizard->getSetupHome()->clickExtensionManager();
        $this->setupWizard->getExtensionsGrid()->waitLoader();
        $this->setupWizard->getExtensionsGrid()->clickInstallButton();

        // Find extension on grid and install
        $assertFindExtensionOnGrid->processAssert($this->setupWizard->getExtensionsInstallGrid(), $extension);
        $this->setupWizard->getExtensionsInstallGrid()->install($extension);

        $this->readinessCheckAndBackup($assertReadiness, $backupOptions);

        // Install Extension
        $assertExtensionAndVersionCheck->processAssert(
            $this->setupWizard,
            $extension,
            AssertExtensionAndVersionCheck::TYPE_INSTALL
        );
        $this->setupWizard->getUpdaterExtension()->clickStartButton();
        $assertSuccessMessage->processAssert(
            $this->setupWizard,
            $extension,
            AssertSuccessMessage::TYPE_INSTALL
        );

        // Open Extension Grid with installed extensions and find installed extension
        $this->setupWizard->open();
        $this->setupWizard->getSetupHome()->clickExtensionManager();
        $this->setupWizard->getExtensionsGrid()->waitLoader();
        $assertFindExtensionOnGrid->processAssert($this->setupWizard->getExtensionsGrid(), $extension);

        // Check version of installed extension
        $assertVersionOnGrid->processAssert(
            $this->setupWizard->getExtensionsGrid(),
            $extension,
            AssertVersionOnGrid::TYPE_INSTALL
        );

        // Update extension
        $this->setupWizard->getExtensionsGrid()->clickUpdateButton($extension);

        $this->readinessCheckAndBackup($assertReadiness, $backupOptions);

        // Update extension
        $assertExtensionAndVersionCheck->processAssert(
            $this->setupWizard,
            $extension,
            AssertExtensionAndVersionCheck::TYPE_UPDATE
        );
        $this->setupWizard->getUpdaterExtension()->clickStartButton();
        $assertSuccessMessage->processAssert(
            $this->setupWizard,
            $extension,
            AssertSuccessMessage::TYPE_UPDATE
        );

        // Open Extension Grid with updated extensions and find updated extension
        $this->setupWizard->open();
        $this->setupWizard->getSetupHome()->clickExtensionManager();
        $this->setupWizard->getExtensionsGrid()->waitLoader();
        $assertFindExtensionOnGrid->processAssert($this->setupWizard->getExtensionsGrid(), $extension);

        // Check version of updated extension
        $assertVersionOnGrid->processAssert(
            $this->setupWizard->getExtensionsGrid(),
            $extension,
            AssertVersionOnGrid::TYPE_UPDATE
        );

        // Uninstall extension
        $this->uninstallExtension(
            $extension,
            $backupOptions,
            $assertReadiness,
            $assertFindExtensionOnGrid,
            $assertExtensionAndVersionCheck,
            $assertSuccessMessage
        );
    }
}
