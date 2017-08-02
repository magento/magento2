<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task;

/**
 * Interface \Magento\Setup\Module\Di\App\Task\OperationInterface
 *
 * @since 2.0.0
 */
interface OperationInterface
{
    /**
     * Processes operation task
     *
     * @return void
     * @since 2.0.0
     */
    public function doOperation();

    /**
     * Returns operation name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();
}
