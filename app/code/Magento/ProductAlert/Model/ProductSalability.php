<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ProductAlert\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Checks product salability
 */
class ProductSalability
{
    /**
     * @param ProductInterface $product
     * @param WebsiteInterface $website
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isSalable(ProductInterface $product, WebsiteInterface $website): bool
    {
        return $product->isSalable();
    }
}
