<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Session;

use \Magento\Framework\Session\Test\Unit\SessionManagerTest;

/**
 * Mock session_regenerate_id to fail if false is passed
 *
 * @param bool $var
 * @return bool
 */
function session_regenerate_id($var)
{
    global $mockPHPFunctions;
    if ($mockPHPFunctions) {
        SessionManagerTest::assertTrue($var);
        return true;
    }
    return call_user_func_array('\session_regenerate_id', func_get_args());
}
