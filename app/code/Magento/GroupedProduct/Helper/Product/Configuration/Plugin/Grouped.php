<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Helper\Product\Configuration\Plugin;

class Grouped
{
    /**
     * Retrieves grouped product options list
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
        if ($typeId == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            $options = [];
            /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $typeInstance */
            $typeInstance = $product->getTypeInstance();
            $associatedProducts = $typeInstance->getAssociatedProducts($product);

            if ($associatedProducts) {
                foreach ($associatedProducts as $associatedProduct) {
                    $qty = $item->getOptionByCode('associated_product_' . $associatedProduct->getId());
                    $option = [
                        'label' => $associatedProduct->getName(),
                        'value' => $qty && $qty->getValue() ? $qty->getValue() : 0,
                    ];
                    $options[] = $option;
                }
            }

            $options = array_merge($options, $proceed($item));
            $isUnConfigured = true;
            foreach ($options as &$option) {
                if ($option['value']) {
                    $isUnConfigured = false;
                    break;
                }
            }
            return $isUnConfigured ? [] : $options;
        }
        return $proceed($item);
    }
}
