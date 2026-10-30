<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Tax\Api\Data;

/**
 * Interface for tax class search results.
 * @api
 * @since 100.0.2
 */
interface TaxClassSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Magento\Tax\Api\Data\TaxClassInterface[]
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \Magento\Tax\Api\Data\TaxClassInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
