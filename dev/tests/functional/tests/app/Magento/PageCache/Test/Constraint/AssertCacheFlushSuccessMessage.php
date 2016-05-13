<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Constraint;

use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCacheFlushSuccessMessage
 */
class AssertCacheFlushSuccessMessage extends AbstractConstraint
{
    /**
     * Assert that success message is displayed after cache flush.
     *
     * @param AdminCache $adminCache
     * @param string $successMessage
     * @return void
     */
    public function processAssert(AdminCache $adminCache, $successMessage)
    {
        \PHPUnit_Framework_Assert::assertEquals(
            $successMessage,
            $adminCache->getMessagesBlock()->getSuccessMessage(),
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
        return 'Flush additional caches are successful.';
    }
}
