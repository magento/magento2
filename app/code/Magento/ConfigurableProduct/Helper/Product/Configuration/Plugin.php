<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Helper\Product\Configuration;

class Plugin
{
    /**
     * Retrieve configuration options for configurable product
     *
     * @param \Magento\Catalog\Helper\Product\Configuration $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetOptions(
        \Magento\Catalog\Helper\Product\Configuration $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
    ) {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        if ($typeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $attributes = $product->getTypeInstance()->getSelectedAttributesInfo($product);
            return array_merge($attributes, $proceed($item));
        }
        return $proceed($item);
    }
}
