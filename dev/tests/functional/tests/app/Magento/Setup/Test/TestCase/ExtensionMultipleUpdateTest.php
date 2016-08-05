<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Setup\Test\Constraint\AssertSuccessfulReadinessCheck;
use Magento\Setup\Test\Constraint\Extension\AssertExtensionAndVersionCheck;
use Magento\Setup\Test\Constraint\Extension\AssertFindExtensionOnGrid;
use Magento\Setup\Test\Constraint\Extension\AssertMultipleUpdateSuccessMessage;
use Magento\Setup\Test\Constraint\Extension\AssertSelectSeveralExtensions;
use Magento\Setup\Test\Constraint\Extension\AssertSuccessMessage;
use Magento\Setup\Test\Fixture\BackupOptions;
use Magento\Setup\Test\Fixture\Extension;
use Magento\Setup\Test\Fixture\RepoCredentials;

/**
 * @group Setup_(CS)
 * @ZephyrId MAGETWO-56328, MAGETWO-56332
 */
class ExtensionMultipleUpdateTest extends AbstractExtensionTest
{
    /**
     * @param FixtureFactory $fixtureFactory
     * @param RepoCredentials $repoCredentials
     * @param BackupOptions $backupOptions
     * @param AssertFindExtensionOnGrid $assertFindExtensionOnGrid
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck
     * @param AssertSuccessMessage $assertSuccessMessage
     * @param AssertMultipleUpdateSuccessMessage $assertMultipleUpdateSuccessMessage
     * @param AssertSelectSeveralExtensions $assertSelectSeveralExtensions
     * @param $needAuthentication
     * @param array $extensions
     */
    public function test(
        FixtureFactory $fixtureFactory,
        RepoCredentials $repoCredentials,
        BackupOptions $backupOptions,
        AssertFindExtensionOnGrid $assertFindExtensionOnGrid,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck,
        AssertSuccessMessage $assertSuccessMessage,
        AssertMultipleUpdateSuccessMessage $assertMultipleUpdateSuccessMessage,
        AssertSelectSeveralExtensions $assertSelectSeveralExtensions,
        $needAuthentication,
        array $extensions
    ) {
        foreach ($extensions as $key => $options) {
            $extensions[$key] = $fixtureFactory->create(Extension::class, $options);
        }

        // Authenticate in admin area
        $this->adminDashboard->open();

        // Open Web Setup Wizard
        $this->setupWizard->open();

        // Authenticate on repo.magento.com
        $this->repoAuthentication($needAuthentication, $repoCredentials);

        foreach ($extensions as $extension) {
            $this->installExtension(
                $extension,
                $backupOptions,
                $assertFindExtensionOnGrid,
                $assertReadiness,
                $assertExtensionAndVersionCheck,
                $assertSuccessMessage
            );
        }

        // Open Extension Grid with extensions to install
        $this->setupWizard->getSetupHome()->clickExtensionManager();
        $this->setupWizard->getExtensionsGrid()->waitLoader();
        $this->setupWizard->getExtensionsGrid()->clickUpdateButton();

        // Select several extensions on grid and check it
        $assertSelectSeveralExtensions->processAssert($this->setupWizard->getExtensionsUpdateGrid(), $extensions);

        // Click general "Update" button
        $this->setupWizard->getExtensionsUpdateGrid()->clickUpdateAllButton();

        $this->readinessCheckAndBackup($assertReadiness, $backupOptions);

        // Start installing
        $this->setupWizard->getUpdaterExtension()->clickStartButton();

        // Check success message
        $assertMultipleUpdateSuccessMessage->processAssert(
            $this->setupWizard,
            $extensions,
            AssertSuccessMessage::TYPE_UPDATE
        );

        // Uninstall installed extensions
        foreach ($extensions as $extension) {
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
}
