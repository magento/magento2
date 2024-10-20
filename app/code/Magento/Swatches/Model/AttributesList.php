<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\ConfigurableProduct\Model\AttributesListInterface;
use Magento\Swatches\Helper\Data as SwatchHelper;

/**
 * @api
 * @since 100.0.2
 */
class AttributesList implements AttributesListInterface
{
    /**
     * @param CollectionFactory $collectionFactory
     * @param SwatchHelper $dataHelper
     */
    public function __construct(
        protected readonly CollectionFactory $collectionFactory,
        protected readonly SwatchHelper $dataHelper
    ) {
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
