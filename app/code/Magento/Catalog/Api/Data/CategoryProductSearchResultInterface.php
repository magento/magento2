<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

interface CategoryProductSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get category product sets list.
     *
     * @return \Magento\Catalog\Api\Data\CategoryProductLinkInterface[]
     */
    public function getItems();

    /**
     * Set category product sets list.
     *
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
