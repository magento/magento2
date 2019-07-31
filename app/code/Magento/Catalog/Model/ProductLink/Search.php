<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Returns collection of product visible in catalog by search key
 */
class Search
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    private $filter;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $catalogVisibility;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogVisibility
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface $filter
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogVisibility,
        \Magento\Ui\DataProvider\AddFilterToCollectionInterface $filter
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->filter = $filter;
        $this->catalogVisibility = $catalogVisibility;
    }

    /**
     * Add required filters and limitations for product collection
     *
     * @param string $searchKey
     * @param int $pageNum
     * @param int $limit
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function prepareCollection(
        string $searchKey,
        int $pageNum,
        int $limit
    ): \Magento\Catalog\Model\ResourceModel\Product\Collection {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addAttributeToSelect(ProductInterface::NAME);
        $productCollection->setPage($pageNum, $limit);
        $this->filter->addFilter($productCollection, 'fulltext', ['fulltext' => $searchKey]);
        $productCollection->setPage($pageNum, $limit);
        return $productCollection;
    }
}
