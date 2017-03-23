<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * Interface for tax rule search results.
 * @api
 */
interface TaxRuleSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \Magento\Tax\Api\Data\TaxRuleInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \Magento\Tax\Api\Data\TaxRuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
