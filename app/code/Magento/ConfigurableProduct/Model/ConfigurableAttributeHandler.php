<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

class ConfigurableAttributeHandler
{
    /**
     * Attribute collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeColFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeColFactory
    ) {
        $this->collectionFactory = $attributeColFactory;
    }

    /**
     * Retrieve list of attributes applicable for configurable product
     *
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    public function getApplicableAttributes()
    {
        /** @var $collection \Magento\Catalog\Model\Resource\Product\Attribute\Collection */
        $collection = $this->collectionFactory->create();
        return $collection->addFieldToFilter(
            'frontend_input',
            'select'
        )->addFieldToFilter(
            'is_user_defined',
            1
        )->addFieldToFilter(
            'is_global',
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL
        );
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     * @return bool
     */
    public function isAttributeApplicable($attribute)
    {
        $types = [
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
        ];
        return !$attribute->getApplyTo() || count(array_diff($types, $attribute->getApplyTo())) === 0;
    }
}
