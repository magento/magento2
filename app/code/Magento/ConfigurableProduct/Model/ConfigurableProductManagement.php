<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\CollectionFactory;

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
     * @var CollectionFactory
     */
    protected $productsFactory;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param ProductVariationsBuilder $productVariationBuilder
     * @param CollectionFactory $productsFactory
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        ProductVariationsBuilder $productVariationBuilder,
        CollectionFactory $productsFactory
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->productVariationBuilder = $productVariationBuilder;
        $this->productsFactory = $productsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function generateVariation(ProductInterface $product, $options)
    {
        $attributes = $this->getAttributesForMatrix($options);
        $products = $this->productVariationBuilder->create($product, $attributes);
        return $products;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount($status = null)
    {
        $products = $this->productsFactory->create();
        // @codingStandardsIgnoreStart
        /** @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection $products */
        // @codingStandardsIgnoreEnd
        switch ($status) {
            case Status::STATUS_ENABLED:
                $products->addAttributeToFilter('status', Status::STATUS_ENABLED);
                break;
            case Status::STATUS_DISABLED:
                $products->addAttributeToFilter('status', Status::STATUS_DISABLED);
                break;
        }
        return $products->getSize();
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
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            $attribute = $this->attributeRepository->get($option->getAttributeId());
            $attributeOptions = $attribute->getOptions() !== null ? $attribute->getOptions() : [];

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
     * @param \Magento\Framework\DataObject $object
     * @return array
     */
    private function objectToArray(\Magento\Framework\DataObject $object)
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
