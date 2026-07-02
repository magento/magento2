<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;

/**
 * Class ConfigurableAttributeData
 * @api
 * @since 100.0.2
 */
class ConfigurableAttributeData
{
    /**
     * Get product attributes
     *
     * @param Product $product
     * @param array $options
     * @return array
     */
    public function getAttributesData(Product $product, array $options = [])
    {
        $defaultValues = [];
        $attributes = [];
        foreach ($product->getTypeInstance()->getConfigurableAttributes($product) as $attribute) {
            $attributeOptionsData = $this->getAttributeOptionsData($attribute, $options);
            if ($attributeOptionsData) {
                $productAttribute = $attribute->getProductAttribute();
                $attributeId = $productAttribute->getId();
                $attributes[$attributeId] = [
                    'id' => $attributeId,
                    'code' => $productAttribute->getAttributeCode(),
                    'label' => $productAttribute->getStoreLabel($product->getStoreId()),
                    'options' => $attributeOptionsData,
                    'position' => $attribute->getPosition(),
                ];
                $defaultValues[$attributeId] = $this->getAttributeConfigValue($attributeId, $product);
            }
        }
        return [
            'attributes' => $attributes,
            'defaultValues' => $defaultValues,
        ];
    }

    /**
     * @param Attribute $attribute
     * @param array $config
     * @return array
     */
    protected function getAttributeOptionsData($attribute, $config)
    {
        $attributeId = $attribute->getAttributeId();
        $usedValues = $this->getUsedAttributeValues($attributeId, $config);
        $attributeOptionsData = [];
        foreach ($attribute->getOptions() as $attributeOption) {
            $optionId = $attributeOption['value_index'];
            $products = $config[$attributeId][$optionId] ?? [];
            // Skip options that are not used by any enabled configurable variation (e.g. only disabled
            // simple products carry them). Values used by enabled but out-of-stock products are kept so
            // that out-of-stock swatches/options are still rendered as unavailable.
            if (empty($products) && !isset($usedValues[(string)$optionId])) {
                continue;
            }
            $attributeOptionsData[] = [
                'id' => $optionId,
                'label' => $attributeOption['label'],
                'products' => $products,
            ];
        }
        return $attributeOptionsData;
    }

    /**
     * Collect attribute values that belong to enabled configurable variations.
     *
     * The index is built only from allowed (enabled) products, so a value missing here means no enabled
     * variation uses it - as opposed to a value used by an enabled product that is merely out of stock.
     *
     * @param int|string $attributeId
     * @param array $config
     * @return array
     */
    private function getUsedAttributeValues($attributeId, array $config)
    {
        $usedValues = [];
        if (isset($config['index']) && is_array($config['index'])) {
            foreach ($config['index'] as $productValues) {
                if (isset($productValues[$attributeId])) {
                    $usedValues[(string)$productValues[$attributeId]] = true;
                }
            }
        }
        return $usedValues;
    }

    /**
     * @param int $attributeId
     * @param Product $product
     * @return mixed|null
     */
    protected function getAttributeConfigValue($attributeId, $product)
    {
        return $product->hasPreconfiguredValues()
            ? $product->getPreconfiguredValues()->getData('super_attribute/' . $attributeId)
            : null;
    }
}
