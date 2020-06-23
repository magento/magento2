<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\DataProvider;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Provides product data by product IDs.
 */
interface ProductDataProviderInterface
{
    /**
     * Retrieve product by product IDs.
     *
     * @param array $productIds
     * @param array $attributeCodes
     * @return ProductInterface[]
     */
    public function getProductByIds(array $productIds, array $attributeCodes): array;
}
