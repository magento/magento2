<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
