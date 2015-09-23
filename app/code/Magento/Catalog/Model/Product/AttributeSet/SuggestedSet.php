<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\AttributeSet;

/**
 * Suggested product attribute set
 */
class SuggestedSet
{
    /**
     * Set collection factory
     *
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\Resource\Helper
     */
    protected $resourceHelper;

    /**
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $product;

    /**
     * @param \Magento\Catalog\Model\Resource\Product $product
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Product $product,
        \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper
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
     */
    public function getSuggestedSets($labelPart)
    {
        $labelPart = $this->resourceHelper->addLikeEscape($labelPart, ['position' => 'any']);
        /** @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection $collection */
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
            \Magento\Eav\Model\Resource\Entity\Attribute\Set\Collection::SORT_ORDER_ASC
        );
        return $collection->getData();
    }
}
