<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

/**
 * Class \Magento\ConfigurableProduct\Model\AttributesList
 *
 */
class AttributesList implements AttributesListInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Retrieve list of attributes
     *
     * @param array $ids
     * @return array
     */
    public function getAttributes($ids)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('main_table.attribute_id', $ids);
        $attributes = [];
        foreach ($collection->getItems() as $attribute) {
            $attributes[] = [
                'id' => $attribute->getId(),
                'label' => $attribute->getFrontendLabel(),
                'code' => $attribute->getAttributeCode(),
                'options' => $attribute->getSource()->getAllOptions(false)
            ];
        }
        return $attributes;
    }
}
