<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Constraint;

use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * class AssertFlushStaticFilesCacheButtonVisibility.
 */
class AssertFlushStaticFilesCacheButtonVisibility extends AbstractConstraint
{
    const FLUSH_STATIC_FILES_CACHE = 'Flush Static Files Cache';

    /**
     * Assert Flush Static Files Cache button visibility.
     *
     * @param AdminCache $adminCache
     * @return void
     */
    public function processAssert(AdminCache $adminCache)
    {
        if ($_ENV['mage_mode'] === 'production') {
            \PHPUnit_Framework_Assert::assertFalse(
                $adminCache->getAdditionalBlock()->isFlushCacheButtonVisible(self::FLUSH_STATIC_FILES_CACHE),
                self::FLUSH_STATIC_FILES_CACHE . ' button should not be visible in production mode.'
            );
        } else {
            \PHPUnit_Framework_Assert::assertTrue(
                $adminCache->getAdditionalBlock()->isFlushCacheButtonVisible(self::FLUSH_STATIC_FILES_CACHE),
                self::FLUSH_STATIC_FILES_CACHE . ' button should be visible in developer or default mode.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return self::FLUSH_STATIC_FILES_CACHE . ' button has correct visibility.';
    }
}
