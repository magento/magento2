<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

class TypesList implements \Magento\Catalog\Api\ProductAttributeTypesListInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory
     */
    private $inputTypeFactory;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeTypeDataBuilder
     */
    private $attributeTypeBuilder;

    /**
     * @param Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Catalog\Api\Data\ProductAttributeTypeDataBuilder $attributeTypeBuilder
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Catalog\Api\Data\ProductAttributeTypeDataBuilder $attributeTypeBuilder
    ) {
        $this->inputTypeFactory = $inputTypeFactory;
        $this->attributeTypeBuilder = $attributeTypeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $types = [];
        $inputType = $this->inputTypeFactory->create();

        foreach ($inputType->toOptionArray() as $option) {
            $types[] = $this->attributeTypeBuilder->populateWithArray($option)->create();
        }
        return $types;
    }
}
