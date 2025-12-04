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
interface CouponSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get rules.
     *
     * @return \Magento\SalesRule\Api\Data\CouponInterface[]
     */
    public function getItems();

    /**
     * Set rules .
     *
     * @param \Magento\SalesRule\Api\Data\CouponInterface[] $items
     * @return $this
     */
    public function setItems(?array $items = null);
}
