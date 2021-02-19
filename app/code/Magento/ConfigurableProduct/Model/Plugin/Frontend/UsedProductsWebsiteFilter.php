<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Plugin\Frontend;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Filter configurable options by current store plugin.
 */
class UsedProductsWebsiteFilter
{
    /**
     * Filter configurable options not assigned to current website.
     *
     * @param Configurable $subject
     * @param ProductInterface $product
     * @param array|null $requiredAttributeIds
     * @param bool|null $skipStock
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetUsedProductsStockFilter(
        Configurable $subject,
        ProductInterface $product,
        ?array $requiredAttributeIds = null,
        ?bool $skipStock = null
    ): void {
        $subject->setStoreFilter($product->getStore(), $product);
    }
}
