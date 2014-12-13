<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GroupedProduct\Model\Product\Initialization\Helper\ProductLinks\Plugin;

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
        if (isset($links['associated']) && !$product->getGroupedReadonly()) {
            $product->setGroupedLinkData((array)$links['associated']);
        }
    }
}
