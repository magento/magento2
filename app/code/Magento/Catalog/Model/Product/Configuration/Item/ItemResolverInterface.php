<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Configuration\Item;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Resolves the product from a configured item.
 *
 * @api
 */
interface ItemResolverInterface
{
    /**
     * Get the final product from a configured item by product type and selection.
     *
     * @param ItemInterface $item
     * @return ProductInterface
     */
    public function getFinalProduct(ItemInterface $item) : ProductInterface;
}
