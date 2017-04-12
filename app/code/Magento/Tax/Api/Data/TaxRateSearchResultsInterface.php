<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for tax rate search results.
 * @api
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
