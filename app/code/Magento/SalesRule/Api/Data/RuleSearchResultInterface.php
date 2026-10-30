<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Api\Data;

/**
 * @api
 * @since 100.0.2
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
    public function setItems(?array $items = null);
}
