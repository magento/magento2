<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Helper\Product\Configuration;

class SaveProductPlugin
{
    /**
     * Unserialize product data for configurable products
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param array $productData
     *
     * @return array
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

        return [$product, $productData];
    }
}
