<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

use Magento\ConfigurableProduct\Model\AttributesListInterface;

class AttributesList implements AttributesListInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\Swatches\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Swatches\Helper\Data $dataHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
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
                'options' => $attribute->getSource()->getAllOptions(false),
                'canCreateOption' => !$this->dataHelper->isSwatchAttribute($attribute),
            ];
        }
        return $attributes;
    }
}
