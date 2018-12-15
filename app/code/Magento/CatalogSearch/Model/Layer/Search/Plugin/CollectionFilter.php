<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Layer\Search\Plugin;

use Magento\Catalog\Model\Category;
use Magento\Search\Model\QueryFactory;
use Magento\CatalogSearch\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class CollectionFilter
{
    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;
    protected $helper;
    protected $collectionFactory;

    /**
     * @param QueryFactory $queryFactory
     */
    public function __construct(QueryFactory $queryFactory, Data $helper, CollectionFactory $collectionFactory)
    {
        $this->queryFactory = $queryFactory;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Add search filter criteria to search collection
     *
     * @param \Magento\Catalog\Model\Layer\Search\CollectionFilter $subject
     * @param null $result
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * @param Category $category
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterFilter(
        \Magento\Catalog\Model\Layer\Search\CollectionFilter $subject,
        $result,
        $collection,
        Category $category
    ) {
        /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();
        if (!$query->isQueryTextShort()) {
            $collection->addSearchFilter($query->getQueryText());
        }
    }

    public function beforeFilter(
        \Magento\Catalog\Model\Layer\Search\CollectionFilter $subject,
        $collection,
        Category $category
    ) {
        /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();
        if ($query->isQueryTextShort() && $this->helper->isMinQueryLength()) {
            return [$this->collectionFactory->create(),$category];
        }

        return [$collection,$category];
    }
}
