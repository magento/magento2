<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\AttributeSet;

/**
 * Suggested product attribute set
 * @since 2.0.0
 */
class SuggestedSet
{
    /**
     * Set collection factory
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     * @since 2.0.0
     */
    protected $attributeSetCollectionFactory;

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\ResourceModel\Helper
     * @since 2.0.0
     */
    protected $resourceHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     * @since 2.0.0
     */
    protected $product;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product $product
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product $product,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
    ) {
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->resourceHelper = $resourceHelper;
        $this->product = $product;
    }

    /**
     * Retrieve list of product attribute sets with search part contained in label
     *
     * @param string $labelPart
     * @return array
     * @since 2.0.0
     */
    public function getSuggestedSets($labelPart)
    {
        $labelPart = $this->resourceHelper->addLikeEscape($labelPart, ['position' => 'any']);
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $collection */
        $collection = $this->attributeSetCollectionFactory->create();
        $collection->setEntityTypeFilter(
            $this->product->getTypeId()
        )->addFieldToFilter(
            'attribute_set_name',
            ['like' => $labelPart]
        )->addFieldToSelect(
            'attribute_set_id',
            'id'
        )->addFieldToSelect(
            'attribute_set_name',
            'label'
        )->setOrder(
            'attribute_set_name',
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection::SORT_ORDER_ASC
        );
        return $collection->getData();
    }
}
