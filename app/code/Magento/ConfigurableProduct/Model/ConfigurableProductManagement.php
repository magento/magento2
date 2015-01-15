<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Model;

class ConfigurableProductManagement implements \Magento\ConfigurableProduct\Api\ConfigurableProductManagementInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var ProductVariationsBuilder
     */
    private $productVariationBuilder;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param ProductVariationsBuilder $productVariationBuilder
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        ProductVariationsBuilder $productVariationBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->productVariationBuilder = $productVariationBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function generateVariation(\Magento\Catalog\Api\Data\ProductInterface $product, $options)
    {
        $attributes = $this->getAttributesForMatrix($options);
        $products = $this->productVariationBuilder->create($product, $attributes);
        return $products;
    }

    /**
     * Prepare attribute info for variation matrix generation
     *
     * @param \Magento\ConfigurableProduct\Api\Data\OptionInterface[] $options
     * @return array
     */
    private function getAttributesForMatrix($options)
    {
        $attributes = [];
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $option */
        foreach ($options as $option) {
            $configurable = $this->objectToArray($option);
            /** @var \Magento\Catalog\Model\Resource\Eav\Attribute $attribute */
            $attribute = $this->attributeRepository->get($option->getAttributeId());
            $attributeOptions = !is_null($attribute->getOptions()) ? $attribute->getOptions() : [];

            foreach ($attributeOptions as $attributeOption) {
                $configurable['options'][] = $this->objectToArray($attributeOption);
            }
            $configurable['attribute_code'] = $attribute->getAttributeCode();
            $attributes[$option->getAttributeId()] = $configurable;
        }
        return $attributes;
    }

    /**
     * Return Data Object data in array format.
     *
     * @param \Magento\Framework\Object $object
     * @return array
     */
    private function objectToArray(\Magento\Framework\Object $object)
    {
        $data = $object->getData();
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                $data[$key] = $this->objectToArray($value);
            } elseif (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    if (is_object($nestedValue)) {
                        $value[$nestedKey] = $this->objectToArray($nestedValue);
                    }
                }
                $data[$key] = $value;
            }
        }
        return $data;
    }
}
