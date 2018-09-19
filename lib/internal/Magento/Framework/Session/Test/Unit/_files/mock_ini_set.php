<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Session;

use \Magento\Framework\Session\Test\Unit\SessionManagerTest;

/**
 * Mock ini_set global function
 *
 * @param string $varName
 * @param string $newValue
 * @return bool|string
 */
function ini_set($varName, $newValue)
{
    global $mockPHPFunctions;
    if ($mockPHPFunctions) {
        SessionManagerTest::$isIniSetInvoked = true;
        SessionManagerTest::assertSame(SessionManagerTest::SESSION_USE_ONLY_COOKIES, $varName);
        SessionManagerTest::assertSame(SessionManagerTest::SESSION_USE_ONLY_COOKIES_ENABLE, $newValue);
        return true;
    }
    return call_user_func_array('\ini_set', func_get_args());
}
