<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetUsedProducts(
        Configurable $subject,
        ProductInterface $product,
        ?array $requiredAttributeIds = null
    ): void {
        $subject->setStoreFilter($product->getStore(), $product);
    }
}
