<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\AdminCache;

/**
 * Assert Cache is Invalidated and Refreshable.
 */
class AssertCacheIsRefreshableAndInvalidated extends AbstractConstraint
{
    /**
     * Success message of refreshed cache.
     */
    const SUCCESS_MESSAGE = '%d cache type(s) refreshed.';

    /**
     * Assert Cache is Invalidated and Refreshable.
     *
     * @param AdminCache $adminCache
     * @param array $cacheTags
     * @return void
     */
    public function processAssert(AdminCache $adminCache, $cacheTags)
    {
        $items = [];
        foreach ($cacheTags as $cacheTag) {
            $items[] = [
                'tags' => $cacheTag,
                'status' => 'Invalidated'
            ];
        }

        $adminCache->open();
        $adminCache->getGridBlock()->massaction($items, 'Refresh');

        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::SUCCESS_MESSAGE, count($items)),
            $adminCache->getMessagesBlock()->getSuccessMessage(),
            'Cache is Invalid and refreshable.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Cache is not Invalid or not refreshable.';
    }
}
