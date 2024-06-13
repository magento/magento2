<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * List of bulk operations. Used for mass save of operations via entity manager.
 * @api
 * @since 100.2.0
 */
interface OperationListInterface
{
    /**
     * Get list of operations.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationInterface[]
     * @since 100.2.0
     */
    public function getItems();
}
