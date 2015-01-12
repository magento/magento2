<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\User\Test\Page\Adminhtml\UserIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUserRoleSalesRestrictedAccess
 */
class AssertUserRoleSalesRestrictedAccess extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    const ROLE_RESOURCE = 'sales';
    const DENIED_ACCESS = 'Access denied';

    /**
     * Asserts that user has only Sales-related permissions
     *
     * @param Dashboard $dashboard
     * @param UserIndex $userIndex
     * @return void
     */
    public function processAssert(
        Dashboard $dashboard,
        UserIndex $userIndex
    ) {
        $menuItems = $dashboard->getMenuBlock()->getTopMenuItems();
        $userIndex->open();
        $deniedMessage = $userIndex->getAccessDeniedBlock()->getTextFromAccessDeniedBlock();
        $isMenuItemSingle = (count($menuItems) == 1);
        $hasSales = in_array(self::ROLE_RESOURCE, $menuItems);
        \PHPUnit_Framework_Assert::assertTrue(
            $hasSales && $isMenuItemSingle && (self::DENIED_ACCESS == $deniedMessage),
            'Sales item is absent in Menu block or possible access to another page, not related to Sales.'
        );
    }

    /**
     * Returns success message if assert true.
     *
     * @return string
     */
    public function toString()
    {
        return 'Sales item is present in Menu block.';
    }
}
