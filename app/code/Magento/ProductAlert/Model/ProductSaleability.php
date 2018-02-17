<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model;

/**
 * Checks product saleability
 *
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
    ) : bool {
        return $product->isSalable();
    }

}
