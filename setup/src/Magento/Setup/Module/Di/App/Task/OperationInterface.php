<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task;

/**
 * Interface \Magento\Setup\Module\Di\App\Task\OperationInterface
 *
 */
interface OperationInterface
{
    /**
     * Processes operation task
     *
     * @return void
     */
    public function doOperation();

    /**
     * Returns operation name
     *
     * @return string
     */
    public function getName();
}
