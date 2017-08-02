<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 2.0.0
 */
interface CategoryAttributeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \Magento\Catalog\Api\Data\CategoryAttributeInterface[]
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \Magento\Catalog\Api\Data\CategoryAttributeInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items);
}
