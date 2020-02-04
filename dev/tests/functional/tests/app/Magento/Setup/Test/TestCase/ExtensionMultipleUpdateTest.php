<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Setup\Test\Constraint\AssertSuccessfulReadinessCheck;
use Magento\Setup\Test\Constraint\Extension\AssertExtensionAndVersionCheck;
use Magento\Setup\Test\Constraint\Extension\AssertFindExtensionOnGrid;
use Magento\Setup\Test\Constraint\Extension\AssertMultipleSuccessMessage;
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
     * @param AssertMultipleSuccessMessage $assertMultipleSuccessMessage
     * @param AssertSelectSeveralExtensions $assertSelectSeveralExtensions
     * @param $needAuthentication
     * @param array $extensions
     * @param array $removeExtensions
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function test(
        FixtureFactory $fixtureFactory,
        RepoCredentials $repoCredentials,
        BackupOptions $backupOptions,
        AssertFindExtensionOnGrid $assertFindExtensionOnGrid,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck,
        AssertSuccessMessage $assertSuccessMessage,
        AssertMultipleSuccessMessage $assertMultipleSuccessMessage,
        AssertSelectSeveralExtensions $assertSelectSeveralExtensions,
        $needAuthentication,
        array $extensions,
        array $removeExtensions
    ) {
        foreach ($extensions as $key => $options) {
            $extensions[$key] = $fixtureFactory->create(Extension::class, $options);
        }

        foreach ($removeExtensions as $key => $options) {
            $removeExtensions[$key] = $fixtureFactory->create(Extension::class, $options);
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

        // Open Extension Grid with extensions to update
        $this->setupWizard->getSetupHome()->clickExtensionManager();
        $this->setupWizard->getExtensionsGrid()->waitLoader();
        $this->setupWizard->getExtensionsGrid()->clickUpdateButton();

        // Select several extensions on grid and check it
        $assertSelectSeveralExtensions->processAssert($this->setupWizard->getExtensionsUpdateGrid(), $extensions);

        // Click general "Update" button
        $this->setupWizard->getExtensionsUpdateGrid()->clickUpdateAllButton();

        $this->readinessCheck($assertReadiness);

        /** @var Extension $removeExtension */
        foreach ($removeExtensions as $removeExtension) {
            $this->setupWizard->getReadiness()->clickRemoveExtension($removeExtension);
            $this->setupWizard->getReadiness()->clickRemoveExtensionOnModal();
        }

        $this->setupWizard->getReadiness()->clickTryAgain();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();
        $this->backup($backupOptions);
        $this->setupWizard->getCreateBackup()->clickNext();

        // Start updating
        $this->setupWizard->getUpdaterExtension()->clickStartButton();

        $updatedExtensions = array_diff_key($extensions, $removeExtensions);

        // Check success message
        $assertMultipleSuccessMessage->processAssert(
            $this->setupWizard,
            $updatedExtensions,
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
