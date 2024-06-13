<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
