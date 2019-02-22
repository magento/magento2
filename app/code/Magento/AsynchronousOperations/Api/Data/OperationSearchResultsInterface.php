<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * Bulk operation search result interface.
 *
 * An bulk is a group of queue messages. An bulk operation item is a queue message.
 * @api
 */
interface OperationSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get list of operations.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationInterface[]
     */
    public function getItems();

    /**
     * Set list of operations.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
