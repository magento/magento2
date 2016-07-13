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
use Magento\Setup\Test\Constraint\AssertFindExtensionOnGrid;

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

    public function test(
        FixtureFactory $fixtureFactory,
        AssertFindExtensionOnGrid $assertFindExtensionOnGrid,
        $installExtension = []
    ) {
        // Create fixture
        $installExtensionFixture = $fixtureFactory->create(InstallExtension::class, ['data' => $installExtension]);

        // Authenticate in admin area
        $this->adminDashboard->open();

        // Open Web Setup Wizard
        $this->setupWizard->open();

        $this->setupWizard->getSetupHome()->clickComponentManager();
        $this->setupWizard->getExtensionsGrid()->clickInstallButton();

        $this->findExtensionOnGrid('magento/sample-data-media');
        $assertFindExtensionOnGrid->processAssert($this->setupWizard, 'magento/sample-data-media');
        $this->setupWizard->getExtensionsInstallGrid()->clickInstall('magento/sample-data-media');
    }

    /**
     * Find Extension on the grid by name
     *
     * @param string $name
     */
    protected function findExtensionOnGrid($name)
    {
        while (true) {
            if ($this->setupWizard->getExtensionsInstallGrid()->isExtensionOnGrid($name)
                || !$this->setupWizard->getExtensionsInstallGrid()->clickNextPageButton()) {
                break;
            }
        }
    }
}
