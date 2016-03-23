<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\AdminCache;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert Flush Static Files Cache button not visible in production mode.
 */
class AssertCacheFlushStaticFilesInProductionMode extends AbstractConstraint
{
    const FLUSH_STATIC_FILES_CACHE = 'Flush Static Files Cache';

    /**
     * Assert Flush Static Files Cache button not visible in production mode.
     *
     * @param AdminCache $adminCache
     * @return void
     */
    public function processAssert(AdminCache $adminCache)
    {
        \PHPUnit_Framework_Assert::assertFalse(
            $adminCache->getAdditionalBlock()->isFlushCacheButtonVisible(self::FLUSH_STATIC_FILES_CACHE),
            self::FLUSH_STATIC_FILES_CACHE . ' button should not be visible in production mode.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return self::FLUSH_STATIC_FILES_CACHE . ' button is not visible in production mode.';
    }
}
