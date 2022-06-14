<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * Dto that holds render information about products
 *
 * @api
 */
interface ProductRenderSearchResultsInterface
{
    /**
     * Get list of products rendered information
     *
     * @return \Magento\Catalog\Api\Data\ProductRenderInterface[]
     */
    public function getItems();

    /**
     * Set list of products rendered information
     *
     * @param \Magento\Catalog\Api\Data\ProductRenderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
