<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
