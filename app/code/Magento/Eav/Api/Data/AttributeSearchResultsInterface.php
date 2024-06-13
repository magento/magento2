<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeSearchResultsInterface
 * @api
 * @since 100.0.2
 */
interface AttributeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
