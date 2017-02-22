<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin;

use Magento\GroupedProduct\Model\Product\Type\Grouped as TypeGrouped;

class Grouped
{
    /**
     * Initialize grouped product links
     *
     * @param \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param array $links
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeInitializeLinks(
        \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $subject,
        \Magento\Catalog\Model\Product $product,
        array $links
    ) {
        if ($product->getTypeId() == TypeGrouped::TYPE_CODE && !$product->getGroupedReadonly()) {
            $links = isset($links['associated']) ? $links['associated'] : $product->getGroupedLinkData();
            $product->setGroupedLinkData((array)$links);
        }
    }
}
