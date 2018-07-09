<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Configuration\Item;

/**
 * Resolves the product for a configured item
 *
 * @api
 */
interface ItemResolverInterface
{
    /**
     * Get the final product from a configured item by product type and selection
     *
     * @param ItemInterface $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getFinalProduct(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item)
    : \Magento\Catalog\Api\Data\ProductInterface;
}
