<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

/**
 * @api
 */
interface CategoryAttributeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Magento\Catalog\Api\Data\CategoryAttributeInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Magento\Catalog\Api\Data\CategoryAttributeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
