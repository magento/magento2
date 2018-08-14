<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Workaround for occasional non-zero exit code (exec returned: 139) caused by the PHP bug
 */
namespace Magento\TestFramework\Workaround;

class Segfault
{
    /**
     * Force garbage collection
     */
    public function endTestSuite()
    {
        gc_collect_cycles();
    }
}
