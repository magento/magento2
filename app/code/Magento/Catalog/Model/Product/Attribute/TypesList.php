<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var \Magento\Catalog\Api\Data\ProductAttributeTypeInterfaceFactory
     */
    private $attributeTypeFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Catalog\Api\Data\ProductAttributeTypeInterfaceFactory $attributeTypeFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Catalog\Api\Data\ProductAttributeTypeInterfaceFactory $attributeTypeFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->inputTypeFactory = $inputTypeFactory;
        $this->attributeTypeFactory = $attributeTypeFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $types = [];
        $inputType = $this->inputTypeFactory->create();

        foreach ($inputType->toOptionArray() as $option) {
            $type = $this->attributeTypeFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $type,
                $option,
                '\Magento\Catalog\Api\Data\ProductAttributeTypeInterface'
            );
            $types[] = $type;
        }
        return $types;
    }
}
