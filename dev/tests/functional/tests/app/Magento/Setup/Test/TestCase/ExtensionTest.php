<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Setup\Test\Page\Adminhtml\SetupWizard;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Setup\Test\Fixture\Extension;
use Magento\Setup\Test\Constraint\Extension\AssertFindExtensionOnGrid;
use Magento\Setup\Test\Constraint\Extension\AssertSuccessMessage;
use Magento\Setup\Test\Constraint\Extension\AssertExtensionAndVersionCheck;
use Magento\Setup\Test\Constraint\AssertSuccessfulReadinessCheck;

/**
 * ExtensionTest checks installing, updating and uninstalling of extensions.
 */
class ExtensionTest extends Injectable
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
        $extensionFixture = $fixtureFactory->create(Extension::class, ['data' => $extensionData]);

        // Authenticate in admin area
        $this->adminDashboard->open();

        // Open Web Setup Wizard
        $this->setupWizard->open();

        // Open Extension Grid with extensions to install
        $this->setupWizard->getSetupHome()->clickComponentManager();
        $this->setupWizard->getExtensionsGrid()->clickInstallButton();

        // Find extension on grid and install
        $assertFindExtensionOnGrid->processAssert($this->setupWizard->getExtensionsInstallGrid(), $extensionFixture);
        $this->setupWizard->getExtensionsInstallGrid()->install($extensionFixture);

        // Readiness Check
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();

        // Create Backup page
        $this->setupWizard->getCreateBackup()->fill($extensionFixture);
        $this->setupWizard->getCreateBackup()->clickNext();

        // Install Extension
        $assertExtensionAndVersionCheck->processAssert($this->setupWizard, $extensionFixture);
        $this->setupWizard->getInstallExtension()->clickInstallButton();
        $assertSuccessMessage->processAssert($this->setupWizard, $extensionFixture);

        // Open Extension Grid with installed extensions and find installed extension
        $this->setupWizard->open();
        $this->setupWizard->getSetupHome()->clickComponentManager();
        $assertFindExtensionOnGrid->processAssert($this->setupWizard->getExtensionsGrid(), $extensionFixture);
    }
}
