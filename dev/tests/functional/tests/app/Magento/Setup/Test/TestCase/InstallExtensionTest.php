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
use Magento\Setup\Test\Fixture\InstallExtension;
use Magento\Setup\Test\Constraint\Extension\AssertFindExtensionOnGrid;
use Magento\Setup\Test\Constraint\Extension\AssertSuccessMessage;
use Magento\Setup\Test\Constraint\Extension\AssertExtensionAndVersionCheck;
use Magento\Setup\Test\Constraint\AssertSuccessfulReadinessCheck;
use Magento\Setup\Test\Block\Extension\AbstractGrid;

class InstallExtensionTest extends Injectable
{
    /**
     * Page System Upgrade Index.
     *
     * @var SetupWizard
     */
    protected $setupWizard;

    /**
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
     * @param array $installExtension
     * @return void
     */
    public function test(
        FixtureFactory $fixtureFactory,
        AssertFindExtensionOnGrid $assertFindExtensionOnGrid,
        AssertSuccessfulReadinessCheck $assertReadiness,
        AssertExtensionAndVersionCheck $assertExtensionAndVersionCheck,
        AssertSuccessMessage $assertSuccessMessage,
        $installExtension = []
    ) {
        $createBackupConfig = array_intersect_key(
            $installExtension,
            ['optionsCode' => '', 'optionsMedia' => '', 'optionsDb' => '']
        );
        $createBackupFixture = $fixtureFactory->create(
            InstallExtension::class,
            ['data' => $createBackupConfig]
        );

        $extension = $installExtension['extension'];
        $version = $installExtension['version'];

        // Authenticate in admin area
        $this->adminDashboard->open();

        // Open Web Setup Wizard
        $this->setupWizard->open();

        // Open Extension Grid with extensions to install
        $this->setupWizard->getSetupHome()->clickComponentManager();
        $this->setupWizard->getExtensionsGrid()->clickInstallButton();

        // Find extension on grid and click install
        $this->findExtensionOnGrid($this->setupWizard->getExtensionsInstallGrid(), $extension);
        $assertFindExtensionOnGrid->processAssert($this->setupWizard->getExtensionsInstallGrid(), $extension);
        $this->setupWizard->getExtensionsInstallGrid()->chooseExtensionVersion($extension, $version);
        $this->setupWizard->getExtensionsInstallGrid()->clickInstall($extension);

        // Readiness Check
        $this->setupWizard->getReadiness()->clickReadinessCheck();
        $assertReadiness->processAssert($this->setupWizard);
        $this->setupWizard->getReadiness()->clickNext();

        // Create Backup page
        $this->setupWizard->getCreateBackup()->fill($createBackupFixture);
        $this->setupWizard->getCreateBackup()->clickNext();

        // Install Extension
        $assertExtensionAndVersionCheck->processAssert($this->setupWizard, $extension, $version);
        $this->setupWizard->getInstallExtension()->clickInstallButton();
        $assertSuccessMessage->processAssert($this->setupWizard, $extension);

        // Open Extension Grid with installed extensions and find installed extension
        $this->setupWizard->open();
        $this->setupWizard->getSetupHome()->clickComponentManager();
        $this->findExtensionOnGrid($this->setupWizard->getExtensionsGrid(), $extension);
        $assertFindExtensionOnGrid->processAssert($this->setupWizard->getExtensionsGrid(), $extension);
    }

    /**
     * Find Extension on the grid by name.
     *
     * @param AbstractGrid $grid
     * @param string $name
     * @return void
     */
    protected function findExtensionOnGrid(AbstractGrid $grid, $name)
    {
        while (true) {
            if ($grid->isExtensionOnGrid($name) || !$grid->clickNextPageButton()) {
                break;
            }
        }
    }
}
