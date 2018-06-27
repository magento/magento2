<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * Interface defines Operation Search Results data object
 *
 * @api
 */
interface OperationSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get operations list.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationInterface[]
     */
    public function getItems();

    /**
     * Set operations list.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
