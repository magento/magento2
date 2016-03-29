<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * Interface for tax class search results.
 * @api
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
