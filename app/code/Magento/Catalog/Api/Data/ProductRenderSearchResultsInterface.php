<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * Dto that holds render information about products
 * @since 2.2.0
 */
interface ProductRenderSearchResultsInterface
{
    /**
     * Get list of products rendered information
     *
     * @return \Magento\Catalog\Api\Data\ProductRenderInterface[]
     * @since 2.2.0
     */
    public function getItems();

    /**
     * Set list of products rendered information
     *
     * @api
     * @param \Magento\Catalog\Api\Data\ProductRenderInterface[] $items
     * @return $this
     * @since 2.2.0
     */
    public function setItems(array $items);
}
