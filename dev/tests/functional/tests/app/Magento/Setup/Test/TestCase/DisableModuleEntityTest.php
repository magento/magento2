<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Setup\Test\Constraint\AssertModule;
use Magento\Setup\Test\Page\Adminhtml\SetupWizard;

/**
 * Class DisableModuleTest
 */
class DisableModuleEntityTest extends Injectable
{
    /**
     * @var Dashboard
     */
    private $adminDashboard;

    /**
     * @var SetupWizard
     */
    private $setupWizard;

    /**
     * @param Dashboard $dashboard
     * @param SetupWizard $setupWizard
     */
    public function __inject(Dashboard $dashboard, SetupWizard $setupWizard)
    {
        $this->adminDashboard = $dashboard;
        $this->setupWizard = $setupWizard;
    }

    public function test(FixtureFactory $fixtureFactory, AssertModule $assertModule)
    {
        // Authenticate in admin area
        $this->adminDashboard->open();

        // Open Web Setup Wizard
        $this->setupWizard->open();

        // Open Modules page
        $this->setupWizard->getModules()->clickModules();

        // Search for module
        $assertModule->processAssert($this->setupWizard, 'magento/module-swatches');
        
        $this->setupWizard->getModulesGrid()->disableModule('magento/module-swatches');
    }
}
