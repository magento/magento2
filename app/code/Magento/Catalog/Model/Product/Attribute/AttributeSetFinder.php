<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\AttributeSetFinderInterface;
use Magento\Framework\DB\Select;

/**
 * Class \Magento\Catalog\Model\Product\Attribute\AttributeSetFinder
 *
 * @since 2.1.0
 */
class AttributeSetFinder implements AttributeSetFinderInterface
{
    /**
     * @var CollectionFactory
     * @since 2.1.0
     */
    private $productCollectionFactory;

    /**
     * @param CollectionFactory $productCollectionFactory
     * @since 2.1.0
     */
    public function __construct(CollectionFactory $productCollectionFactory)
    {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function findAttributeSetIdsByProductIds(array $productIds)
    {
        /** @var $collection Collection */
        $collection = $this->productCollectionFactory->create();
        $select = $collection
            ->getSelect()
            ->reset(Select::COLUMNS)
            ->columns(ProductInterface::ATTRIBUTE_SET_ID)
            ->where('entity_id IN (?)', $productIds)
            ->group(ProductInterface::ATTRIBUTE_SET_ID);
        $result = $collection->getConnection()->fetchCol($select);
        return $result;
    }
}
