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
     * @param array $result
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetOptions(
        \Magento\Catalog\Helper\Product\Configuration $subject,
        $result,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
    ) {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        if ($typeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $attributes = $product->getTypeInstance()->getSelectedAttributesInfo($product);
            return array_merge($attributes, $result);
        }
        return $result;
    }
}
