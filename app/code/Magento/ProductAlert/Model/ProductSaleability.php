<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\Website;

/**
 * Checks product saleability
 */
class ProductSaleability
{
    /**
     * @param ProductInterface $product
     * @param Website $website
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isSalable(ProductInterface $product, Website $website): bool
    {
        return $product->isSalable();
    }
}
