<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\PageCache\Test\Page\Adminhtml\AdminCache;

/**
 * Steps:
 * 1. Log in to backend.
 * 2. Navigate through menu to cache management page.
 * 3. Perform asserts.
 *
 * @ZephyrId MAGETWO-39934
 */
class FlushStaticFilesCacheButtonVisibilityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'PS';
    /* end tags */
    
    /**
     * Check 'Flush Static Files Cache' not visible in production mode.
     *
     *
     * @param AdminCache $adminCache
     * @return void
     */
    public function test(AdminCache $adminCache)
    {
        $adminCache->open();
    }
}
