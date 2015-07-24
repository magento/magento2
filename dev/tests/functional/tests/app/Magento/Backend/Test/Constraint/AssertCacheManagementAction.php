<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Backend\Test\Fixture\GlobalSearch;
use Magento\Backend\Test\Page\Adminhtml\AdminCache;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert Cache Management Action.
 */
class AssertCacheManagementAction extends AbstractConstraint
{
    /**
     * Assert that backend page has correct title and 404 Error is absent on the page.
     *
     * @param AdminCache $adminCache
     * @param string $successMessage
     * @return void
     */
    public function processAssert(AdminCache $adminCache, $successMessage)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $successMessage,
            $adminCache->getMessagesBlock()->getSuccessMessages(),
            'Action is not successful.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Cache management action is successful.';
    }
}
