<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin\Handler\ProductType;

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface;
use Magento\Catalog\Model\Product;

class Configurable implements HandlerInterface
{
    /**
     * Handle data received from Associated Products tab of configurable product
     *
     * @param Product $product
     * @return void
     */
    public function handle(Product $product)
    {
        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return;
        }

        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $type */
        $type = $product->getTypeInstance();
        $originalAttributes = $type->getConfigurableAttributesAsArray($product);
        // Organize main information about original product attributes in assoc array form
        $originalAttributesMainInfo = [];
        if (is_array($originalAttributes)) {
            foreach ($originalAttributes as $originalAttribute) {
                $originalAttributesMainInfo[$originalAttribute['id']] = [];
                foreach ($originalAttribute['values'] as $value) {
                    $originalAttributesMainInfo[$originalAttribute['id']][$value['value_index']] = [
                        'is_percent' => $value['is_percent'],
                        'pricing_value' => $value['pricing_value'],
                    ];
                }
            }
        }
        $attributeData = $product->getConfigurableAttributesData();
        if (is_array($attributeData)) {
            foreach ($attributeData as &$data) {
                $id = $data['attribute_id'];
                foreach ($data['values'] as &$value) {
                    $valueIndex = $value['value_index'];
                    if (isset($originalAttributesMainInfo[$id][$valueIndex])) {
                        $value['pricing_value'] = $originalAttributesMainInfo[$id][$valueIndex]['pricing_value'];
                        $value['is_percent'] = $originalAttributesMainInfo[$id][$valueIndex]['is_percent'];
                    } else {
                        $value['pricing_value'] = 0;
                        $value['is_percent'] = 0;
                    }
                }
            }
            $product->setConfigurableAttributesData($attributeData);
        }
    }
}
