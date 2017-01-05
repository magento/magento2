<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert menu item availability.
 */
class AssertMenuItemNotVisible extends AbstractConstraint
{
    /**
     * Assert that menu item is not visible in dashboard menu.
     *
     * @param Dashboard $dashboard
     * @param string $menuItem
     * @return void
     */
    public function processAssert(Dashboard $dashboard, $menuItem)
    {
        $dashboard->open();

        \PHPUnit_Framework_Assert::assertFalse(
            $dashboard->getMenuBlock()->isMenuItemVisible($menuItem),
            'Menu item ' . $menuItem . '  is supposed to be not visible.'
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Menu item is not visible in dashboard menu.';
    }
}
