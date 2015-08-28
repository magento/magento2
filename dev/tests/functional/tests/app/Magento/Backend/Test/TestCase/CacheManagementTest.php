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
 * 2. Navigate throught menu to cache management page.
 * 3. Click a button.
 * 4. Perform asserts.
 *
 * @ZephyrId MAGETWO-34502, MAGETWO-34503, MAGETWO-39934
 */
class CacheManagementTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * Open admin cache management page.
     *
     * @param AdminCache $adminCache
     * @param string $flushButtonName
     * @return void
     */
    public function test(AdminCache $adminCache, $flushButtonName)
    {
        $adminCache->open();
        $adminCache->getAdditionalBlock()->clickFlushCache($flushButtonName);
    }
}
