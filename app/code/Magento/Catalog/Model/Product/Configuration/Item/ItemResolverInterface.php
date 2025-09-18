<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Configuration\Item;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Resolves the product from a configured item.
 *
 * @api
 * @since 102.0.7
 */
interface ItemResolverInterface
{
    /**
     * Get the final product from a configured item by product type and selection.
     *
     * @param ItemInterface $item
     * @return ProductInterface
     * @since 102.0.7
     */
    public function getFinalProduct(ItemInterface $item) : ProductInterface;
}
