<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Tax\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for tax rate search results.
 * @api
 * @since 100.0.2
 */
interface TaxRateSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Magento\Tax\Api\Data\TaxRateInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Magento\Tax\Api\Data\TaxRateInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
