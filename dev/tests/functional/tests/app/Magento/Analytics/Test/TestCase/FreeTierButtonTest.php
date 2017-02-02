<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Steps:
 * 1. Log in to backend.
 * 2. Click on page actions button.
 * 3. Perform asserts.
 *
 * @ZephyrId MAGETWO-34874
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
