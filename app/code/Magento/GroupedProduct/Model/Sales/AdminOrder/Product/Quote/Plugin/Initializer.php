<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product quote initializer plugin
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 */
namespace Magento\GroupedProduct\Model\Sales\AdminOrder\Product\Quote\Plugin;

use Magento\GroupedProduct\Model\Product\Type\Grouped;

class Initializer
{
    /**
     * @param \Magento\Sales\Model\AdminOrder\Product\Quote\Initializer $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $config
     *
     * @return \Magento\Quote\Model\Quote\Item|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundInit(
        \Magento\Sales\Model\AdminOrder\Product\Quote\Initializer $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\DataObject $config
    ) {
        $item = $proceed($quote, $product, $config);

        if (is_string($item) && $product->getTypeId() != Grouped::TYPE_CODE) {
            $item = $quote->addProduct(
                $product,
                $config,
                \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_LITE
            );
        }
        return $item;
    }
}
