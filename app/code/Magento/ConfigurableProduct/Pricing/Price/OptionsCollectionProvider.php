<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * A provider of configurable options collection.
 */
class OptionsCollectionProvider
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resourceConnection;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Prepares and provides collection.
     *
     * @param Select[] $linkedProductSelects
     * @param string[] $attributesToSelect
     *
     * @return Collection
     */
    public function getCollection(array $linkedProductSelects, array $attributesToSelect = ['price', 'special_price'])
    {
        $productIds = $this->resource->getConnection()->fetchCol(
            '(' . implode(') UNION (', $linkedProductSelects) . ')'
        );

        return $this->collectionFactory->create()
            ->addAttributeToSelect($attributesToSelect)
            ->addIdFilter($productIds);
    }
}
