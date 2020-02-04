<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api\Data;

/**
 * Interface CategoryProductSearchResultInterface
 * @api
 * @since 101.0.0
 */
interface CategoryProductSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get category product sets list.
     *
     * @return \Magento\Catalog\Api\Data\CategoryProductLinkInterface[]
     * @since 101.0.0
     */
    public function getItems();

    /**
     * Set category product sets list.
     *
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkInterface[] $items
     * @return $this
     * @since 101.0.0
     */
    public function setItems(array $items);
}
