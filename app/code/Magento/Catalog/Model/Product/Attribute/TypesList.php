<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

/**
 * Class \Magento\Catalog\Model\Product\Attribute\TypesList
 *
 * @since 2.0.0
 */
class TypesList implements \Magento\Catalog\Api\ProductAttributeTypesListInterface
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory
     * @since 2.0.0
     */
    private $inputTypeFactory;

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeTypeInterfaceFactory
     * @since 2.0.0
     */
    private $attributeTypeFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     * @since 2.0.0
     */
    private $dataObjectHelper;

    /**
     * @param Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Catalog\Api\Data\ProductAttributeTypeInterfaceFactory $attributeTypeFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @since 2.0.0
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
     * @since 2.0.0
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
                \Magento\Catalog\Api\Data\ProductAttributeTypeInterface::class
            );
            $types[] = $type;
        }
        return $types;
    }
}
