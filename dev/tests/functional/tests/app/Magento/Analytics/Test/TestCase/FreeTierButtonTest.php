<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\TestCase;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Log in to backend.
 * 2. Click on page actions button.
 * 3. Perform asserts.
 *
 * @ZephyrId MAGETWO-63715
 */
class FreeTierButtonTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Run menu navigation test.
     *
     * @param Dashboard $dashboard
     * @return void
     */
    public function test(Dashboard $dashboard)
    {
        $dashboard->open();
        $dashboard->getPageActionsBlock()->click();
    }
}
