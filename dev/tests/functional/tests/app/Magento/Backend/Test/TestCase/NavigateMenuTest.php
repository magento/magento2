<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Steps:
 * 1. Log in to backend.
 * 2. Navigate through menu to the page.
 * 3. Perform asserts.
 *
 * @ZephyrId MAGETWO-34874
 */
class NavigateMenuTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Run menu navigation test.
     *
     * @param Dashboard $dashboard
     * @param string $menuItem
     * @param bool $waitMenuItemNotVisible
     * @return void
     */
    public function test(Dashboard $dashboard, $menuItem, $waitMenuItemNotVisible = true)
    {
        $dashboard->open();
        $dashboard->getMenuBlock()->navigate($menuItem, $waitMenuItemNotVisible);
    }
}
