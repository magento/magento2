<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Adminhtml\SystemAccount;

class AccountSettingsPageTest extends Injectable
{
    /* tags */
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * "Account settings" page in Admin panel.
     *
     * @var SystemAccount
     */
    protected $systemAccountPage;

    /**
     * Prepare data for further test execution.
     *
     * @param SystemAccount $systemAccountPage
     * @return void
     */
    public function __inject(SystemAccount $systemAccountPage)
    {
        $this->systemAccountPage = $systemAccountPage;
    }

    /**
     * Test execution.
     *
     * @return void
     */
    public function test()
    {
        $this->systemAccountPage->open();
    }
}
