<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Adminhtml\AdminCache;

/**
 * Steps:
 * 1. Log in to backend.
 * 2. Navigate through menu to cache management page.
 * 3. Click a button.
 * 4. Perform asserts.
 *
 * @ZephyrId MAGETWO-34052, MAGETWO-34053, MAGETWO-39934
 */
class CacheManagementTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * Open admin cache management page and click button to flush cache.
     *
     * @param AdminCache $adminCache
     * @param string $flushButtonName
     * @return void
     */
    public function test(AdminCache $adminCache, $flushButtonName)
    {
        /**
         * Skip test for 'Flush Static Files Cache' in production mode.
         */
        if (($flushButtonName === 'Flush Static Files Cache') && $_ENV['mage_mode'] === 'production') {
            $this->markTestSkipped('Skip flushing static files cache test when in production mode.');
        }
        $adminCache->open();
        $adminCache->getAdditionalBlock()->clickFlushCache($flushButtonName);
    }
}
