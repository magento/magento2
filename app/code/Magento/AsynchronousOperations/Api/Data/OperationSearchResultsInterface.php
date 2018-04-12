<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Api\Data;

/**
 * @api
 */
interface OperationSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Magento\AsynchronousOperations\Api\Data\OperationInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
