<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
