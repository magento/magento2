<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Api\Data;

/**
 * @api
 */
interface RuleSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get rules.
     *
     * @return \Magento\SalesRule\Api\Data\RuleInterface[]
     */
    public function getItems();

    /**
     * Set rules .
     *
     * @param \Magento\SalesRule\Api\Data\RuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items = null);
}
