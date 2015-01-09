<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Tools\Di\App\Task;

interface OperationInterface
{
    /**
     * Processes operation task
     *
     * @return void
     */
    public function doOperation();
}
