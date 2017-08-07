<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\DataProvider\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;

/**
 * Allows to collect absolutely different product render information from different modules
 *
 * @api
 * @since 2.2.0
 */
interface ProductRenderCollectorInterface
{
    /**
     * Takes information from Product, map to render information and hydrate render object
     *
     * @param ProductInterface $product
     * @param ProductRenderInterface $productRender
     * @param array $data
     * @return void
     * @since 2.2.0
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender);
}
