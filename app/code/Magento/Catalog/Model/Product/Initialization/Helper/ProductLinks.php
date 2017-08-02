<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Initialization\Helper;

/**
 * Class \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks
 *
 */
class ProductLinks
{
    /**
     * Init product links data (related, upsell, cross sell)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $links link data
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function initializeLinks(\Magento\Catalog\Model\Product $product, array $links)
    {
        return $product;
    }
}
