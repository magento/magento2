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
 * 3. Perform asserts.
 *
 * @ZephyrId MAGETWO-39934
 */
class CacheFlushStaticFilesInProductionModeTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * Test only for 'Flush Static Files Cache' in production mode.
     *
     * @return void
     */
    public function __prepare()
    {
        if ($_ENV['mage_mode'] !== 'production') {
            $this->markTestSkipped('Skip "Flush Static Files Cache" button absence test when not in production mode.');
        }
    }

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
