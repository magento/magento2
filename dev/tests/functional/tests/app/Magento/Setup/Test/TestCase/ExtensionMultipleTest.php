<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\TestCase;

use Magento\Setup\Test\Constraint\Extension\AssertFindExtensionOnGrid;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Setup\Test\Fixture\Extension;
use Magento\Setup\Test\Fixture\BackupOptions;
use Magento\Setup\Test\Fixture\RepoCredentials;
use Magento\Setup\Test\Constraint\AssertSuccessfulReadinessCheck;
use Magento\Setup\Test\Constraint\Extension\AssertSuccessMessage;
use Magento\Setup\Test\Constraint\Extension\AssertMultipleSuccessMessage;
use Magento\Setup\Test\Constraint\Extension\AssertMultipleExtensionAndVersionCheck;
use Magento\Setup\Test\Constraint\Extension\AssertExtensionAndVersionCheck;
use Magento\Setup\Test\Constraint\Extension\AssertSelectSeveralExtensions;

/**
 * ExtensionMultipleTest checks installing of several extensions
 */
class ExtensionMultipleTest extends AbstractExtensionTest
{
    /**
     * @param FixtureFactory $fixtureFactory
     * @param AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck
     * @param AssertMultipleExtensionAndVersionCheck $assertMultipleExtensionAndVersionCheck
     * @param AssertSuccessMessage $assertSuccessMessage
     * @param AssertMultipleSuccessMessage $assertMultipleSuccessMessage
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param AssertSelectSeveralExtensions $assertSelectSeveralExtensions
     * @param AssertFindExtensionOnGrid $assertFindExtensionOnGrid
     * @param bool $needAuthentication
     * @param RepoCredentials $repoCredentials
     * @param BackupOptions $backupOptions
     * @param Extension[] $extensions
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function test(
        FixtureFactory $fixtureFactory,
        AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck,
        AssertMultipleExtensionAndVersionCheck $assertMultipleExtensionAndVersionCheck,
        AssertSuccessMessage $assertSuccessMessage,
        AssertMultipleSuccessMessage $assertMultipleSuccessMessage,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertSelectSeveralExtensions $assertSelectSeveralExtensions,
        AssertFindExtensionOnGrid $assertFindExtensionOnGrid,
        $needAuthentication,
        RepoCredentials $repoCredentials,
        BackupOptions $backupOptions,
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

        // Open Extension Grid with extensions to install
        $this->setupWizard->getSetupHome()->clickExtensionManager();
        $this->setupWizard->getExtensionsGrid()->waitLoader();
        $this->setupWizard->getExtensionsGrid()->clickInstallButton();

        // Select several extensions on grid and check it
        $assertSelectSeveralExtensions->processAssert($this->setupWizard->getExtensionsInstallGrid(), $extensions);

        // Click general "Install" button
        $this->setupWizard->getExtensionsInstallGrid()->clickInstallAll();

        $this->readinessCheckAndBackup($assertReadiness, $backupOptions);

        // Check selected extensions
        $assertMultipleExtensionAndVersionCheck->processAssert(
            $this->setupWizard,
            $extensions,
            AssertExtensionAndVersionCheck::TYPE_INSTALL
        );

        // Start installing
        $this->setupWizard->getUpdaterExtension()->clickStartButton();

        // Check success message
        $assertMultipleSuccessMessage->processAssert(
            $this->setupWizard,
            $extensions,
            AssertSuccessMessage::TYPE_INSTALL
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
