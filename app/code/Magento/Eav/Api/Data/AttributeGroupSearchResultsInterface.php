<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeGroupSearchResultsInterface
 * @api
 * @since 100.0.2
 */
interface AttributeGroupSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attribute sets list.
     *
     * @return \Magento\Eav\Api\Data\AttributeGroupInterface[]
     */
    public function getItems();

    /**
     * Set attribute sets list.
     *
     * @param \Magento\Eav\Api\Data\AttributeGroupInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
