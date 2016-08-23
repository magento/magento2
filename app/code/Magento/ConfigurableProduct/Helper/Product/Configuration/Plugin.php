<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
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

    /**
     * Unserialize product data for configurable products
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $product
     * @oaram array $productData
     */
    public function beforeInitializeFromData(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $product,
        array $productData
    ) {
        if (isset($productData["configurable-matrix-serialized"])) {
            $configurableMatrixSerialized = $productData["configurable-matrix-serialized"];
            if (!empty($configurableMatrixSerialized)) {
                $productData["configurable-matrix"] = json_decode($configurableMatrixSerialized, true);
                unset($productData["configurable-matrix-serialized"]);
            }
        }
        if (isset($productData["associated_product_ids_serialized"])) {
            $associatedProductIdsSerialized = $productData["associated_product_ids_serialized"];
            if (!empty($associatedProductIdsSerialized)) {
                $productData["associated_product_ids"] = json_decode($associatedProductIdsSerialized, true);
                unset($productData["associated_product_ids_serialized"]);
            }
        }
    }
}
