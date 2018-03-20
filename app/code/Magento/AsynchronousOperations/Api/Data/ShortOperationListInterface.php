<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * List of bulk operations with short operation details.
 * @api
 * @since 100.3.0
 */
interface ShortOperationListInterface
{
    /**
     * Get list of operations.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationShortDetailsInterface[]
     * @since 100.3.0
     */
    public function getItems();
}
