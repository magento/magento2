<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\DataProvider;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Provides product data by product ID.
 */
interface ProductDataProviderInterface
{
    /**
     * Retrieve product by product ID.
     *
     * @param int $productId
     * @param array $attributeCodes
     * @return ProductInterface
     */
    public function getProductById(int $productId, array $attributeCodes);
}
