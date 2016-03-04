<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace  Magento\Upgrade\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Upgrade\Test\Page\Adminhtml\SetupWizard;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

class UpgradeSystemTest extends Injectable
{

    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

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
     * @param array $upgrade
     * @return void
     */
    public function test(
        $upgrade = []
    )
    {
        $this->adminDashboard->open();
        $this->setupWizard->open();
        $this->setupWizard->getSystemUpgrade()->clickSystemUpgrade();

        // TODO: This sleep should be removed
        sleep(10);
    }

}
