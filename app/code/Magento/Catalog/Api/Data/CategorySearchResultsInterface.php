<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 * @since 102.0.0
 */
interface CategorySearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get categories
     *
     * @return \Magento\Catalog\Api\Data\CategoryInterface[]
     * @since 102.0.0
     */
    public function getItems();

    /**
     * Set categories
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface[] $items
     * @return $this
     * @since 102.0.0
     */
    public function setItems(array $items);
}
