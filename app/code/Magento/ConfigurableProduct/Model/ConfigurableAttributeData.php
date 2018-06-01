<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\Config\Source\ProductAttributesSort;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class ConfigurableAttributeData
 * @api
 * @since 100.0.2
 */
class ConfigurableAttributeData
{
    const XML_PATH_CONF_PRODUCT_ATTRIBUTES_SORT = 'catalog/frontend/product_attributes_sort_by';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

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
                    'position' => $this->getPosition($attribute),
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
     * Get configurable product attribute position
     *
     * @param $attribute
     *
     * @return mixed
     */
    protected function getPosition($attribute)
    {
        if ($this->getConfigurableProductAttributesSortConfig() === ProductAttributesSort::GLOBAL_ATTRIBUTE_SETTING) {
            return $attribute->getProductAttribute()->getPosition();
        }

        return $attribute->getPosition();
    }

    /**
     * Get configuration value for configurable products attributes sorting settings
     *
     * @return int
     */
    protected function getConfigurableProductAttributesSortConfig()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CONF_PRODUCT_ATTRIBUTES_SORT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param Attribute $attribute
     * @param array $config
     * @return array
     */
    protected function getAttributeOptionsData($attribute, $config)
    {
        $attributeOptionsData = [];
        foreach ($attribute->getOptions() as $attributeOption) {
            $optionId = $attributeOption['value_index'];
            $attributeOptionsData[] = [
                'id' => $optionId,
                'label' => $attributeOption['label'],
                'products' => isset($config[$attribute->getAttributeId()][$optionId])
                    ? $config[$attribute->getAttributeId()][$optionId]
                    : [],
            ];
        }
        return $attributeOptionsData;
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
