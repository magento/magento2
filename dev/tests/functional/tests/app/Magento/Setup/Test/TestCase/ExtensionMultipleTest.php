<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\TestCase;

use Magento\Setup\Test\Constraint\Extension\AssertFindExtensionOnGrid;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Setup\Test\Fixture\Extension;
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
     * @param $extensions
     * @param array $extensionData
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
        array $extensions,
        array $extensionData = []
    ) {
        foreach ($extensions as $key => $extension) {
            $extensions[$key] = $fixtureFactory->create(
                Extension::class,
                ['data' => array_merge($extension, $extensionData)]
            );
        }
        
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

        // Select several extensions on grid and check it.
        $assertSelectSeveralExtensions->processAssert($this->setupWizard->getExtensionsInstallGrid(), $extensions);

        // Click general "Install" button.
        $this->setupWizard->getExtensionsInstallGrid()->clickInstallAll();

        $this->readinessCheckAndBackup($assertReadiness, $extensionFixture);

        // Check selected extensions.
        $assertMultipleExtensionAndVersionCheck->processAssert(
            $this->setupWizard,
            $extensions,
            AssertExtensionAndVersionCheck::TYPE_INSTALL
        );

        // Start installing.
        $this->setupWizard->getUpdaterExtension()->clickStartButton();

        // Check success message.
        $assertMultipleSuccessMessage->processAssert(
            $this->setupWizard,
            $extensions,
            AssertSuccessMessage::TYPE_INSTALL
        );

        // Uninstall installed extensions.
        foreach ($extensions as $extension) {
            $this->uninstallExtension(
                $extension,
                $assertReadiness,
                $assertFindExtensionOnGrid,
                $assertExtensionAndVersionCheck,
                $assertSuccessMessage
            );
        }
    }
}
