<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Eav\Api\Data;

/**
 * Interface AttributeSetSearchResultsInterface
 * @api
 * @since 100.0.2
 */
interface AttributeSetSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attribute sets list.
     *
     * @return \Magento\Eav\Api\Data\AttributeSetInterface[]
     */
    public function getItems();

    /**
     * Set attribute sets list.
     *
     * @param \Magento\Eav\Api\Data\AttributeSetInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
