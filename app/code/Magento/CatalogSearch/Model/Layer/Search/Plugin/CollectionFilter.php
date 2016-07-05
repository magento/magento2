<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Layer\Search\Plugin;

use Magento\Catalog\Model\Category;
use Magento\Search\Model\QueryFactory;

class CollectionFilter
{
    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    protected $queryFactory;

    /**
     * @param QueryFactory $queryFactory
     */
    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    /**
     * Add search filter criteria to search collection
     *
     * @param \Magento\Catalog\Model\Layer\Search\CollectionFilter $subject
     * @param \Closure $proceed
     * @param \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection
     * @param Category $category
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundFilter(
        \Magento\Catalog\Model\Layer\Search\CollectionFilter $subject,
        \Closure $proceed,
        $collection,
        Category $category
    ) {
        $proceed($collection, $category);
        /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();
        if (!$query->isQueryTextShort()) {
            $collection->addSearchFilter($query->getQueryText());
        }
    }
}
