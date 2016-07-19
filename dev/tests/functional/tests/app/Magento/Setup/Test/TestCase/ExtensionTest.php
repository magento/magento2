<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Setup\Test\Fixture\Extension;
use Magento\Setup\Test\Constraint\Extension\AssertFindExtensionOnGrid;
use Magento\Setup\Test\Constraint\Extension\AssertSuccessMessage;
use Magento\Setup\Test\Constraint\Extension\AssertExtensionAndVersionCheck;
use Magento\Setup\Test\Constraint\AssertSuccessfulReadinessCheck;

/**
 * ExtensionTest checks installing, updating and uninstalling of extensions.
 */
class ExtensionTest extends AbstractExtensionTest
{
    /**
     * @param FixtureFactory $fixtureFactory
     * @param AssertFindExtensionOnGrid $assertFindExtensionOnGrid
     * @param AssertSuccessfulReadinessCheck $assertReadiness
     * @param AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck
     * @param AssertSuccessMessage $assertSuccessMessage
     * @param array $extensionData
     * @return void
     */
    public function test(
        FixtureFactory $fixtureFactory,
        AssertFindExtensionOnGrid $assertFindExtensionOnGrid,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck,
        AssertSuccessMessage $assertSuccessMessage,
        $extensionData = []
    ) {
        /** @var Extension $extensionFixture */
        $extensionFixture = $fixtureFactory->create(Extension::class, ['data' => $extensionData]);

        // Authenticate in admin area
        $this->adminDashboard->open();

        // Open Web Setup Wizard
        $this->setupWizard->open();

        // Authenticate on repo.magento.com
        if ($extensionFixture->getNeedAuthentication() === 'Yes') {
            $this->setupWizard->getSystemConfig()->clickSystemConfig();
            $this->setupWizard->getAuthentication()->fill($extensionFixture);
            $this->setupWizard->getAuthentication()->clickSaveConfig();
            $this->setupWizard->open();
        }

        // Open Extension Grid with extensions to install
        $this->setupWizard->getSetupHome()->clickExtensionManager();
        $this->setupWizard->getExtensionsGrid()->clickInstallButton();

        // Find extension on grid and install
        $assertFindExtensionOnGrid->processAssert($this->setupWizard->getExtensionsInstallGrid(), $extensionFixture);
        $this->setupWizard->getExtensionsInstallGrid()->install($extensionFixture);

        $this->readinessCheckAndBackup($assertReadiness, $extensionFixture);

        // Install Extension
        $assertExtensionAndVersionCheck->processAssert(
            $this->setupWizard,
            $extensionFixture,
            AssertExtensionAndVersionCheck::TYPE_INSTALL
        );
        $this->setupWizard->getUpdaterExtension()->clickStartButton();
        $assertSuccessMessage->processAssert(
            $this->setupWizard,
            $extensionFixture,
            AssertSuccessMessage::TYPE_INSTALL
        );

        // Open Extension Grid with installed extensions and find installed extension
        $this->setupWizard->open();
        $this->setupWizard->getSetupHome()->clickExtensionManager();
        $assertFindExtensionOnGrid->processAssert($this->setupWizard->getExtensionsGrid(), $extensionFixture);

        // Check version of installed extension
        $versionOnGrid = $this->setupWizard->getExtensionsGrid()->getVersion($extensionFixture);
        if ($extensionFixture->getVersion() != $versionOnGrid) {
            $this->fail('Version of installed extension is incorrect!');
        }

        // Update extension
        $this->setupWizard->getExtensionsGrid()->clickUpdateButton($extensionFixture);

        $this->readinessCheckAndBackup($assertReadiness, $extensionFixture);

        // Update extension
        $assertExtensionAndVersionCheck->processAssert(
            $this->setupWizard,
            $extensionFixture,
            AssertExtensionAndVersionCheck::TYPE_UPDATE
        );
        $this->setupWizard->getUpdaterExtension()->clickStartButton();
        $assertSuccessMessage->processAssert(
            $this->setupWizard,
            $extensionFixture,
            AssertSuccessMessage::TYPE_UPDATE
        );

        // Open Extension Grid with updated extensions and find updated extension
        $this->setupWizard->open();
        $this->setupWizard->getSetupHome()->clickExtensionManager();
        $assertFindExtensionOnGrid->processAssert($this->setupWizard->getExtensionsGrid(), $extensionFixture);

        // Check version of updated extension
        $versionOnGrid = $this->setupWizard->getExtensionsGrid()->getVersion($extensionFixture);
        if ($extensionFixture->getVersionToUpdate() != $versionOnGrid) {
            $this->fail('Version of updated extension is incorrect!');
        }

        // Uninstall extension.
        $this->uninstallExtension(
            $extensionFixture,
            $assertReadiness,
            $assertFindExtensionOnGrid,
            $assertExtensionAndVersionCheck,
            $assertSuccessMessage
        );
    }
}
