<?php

declare(strict_types=1);

namespace Chizhov\Status\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/** @api */
interface CustomerStatusSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get customer statuses list.
     *
     * @return \Chizhov\Status\Api\Data\CustomerStatusInterface[]
     */
    public function getItems();

    /**
     * Set customer statuses list.
     *
     * @param \Chizhov\Status\Api\Data\CustomerStatusInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
