<?php

namespace Magento\ProductAlert\Model;

/**
 * Class ProductSaleability
 */
class ProductSaleability
{

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Store\Model\Website $website
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isSalable(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Store\Model\Website $website
    ) {
        return (bool)$product->isSalable();
    }

}