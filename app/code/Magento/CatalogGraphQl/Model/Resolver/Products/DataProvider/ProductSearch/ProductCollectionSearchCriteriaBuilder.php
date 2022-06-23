<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch;

use Magento\Catalog\Model\CategoryProductLink;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Builds a search criteria intended for the product collection based on search criteria used on SearchAPI
 */
class ProductCollectionSearchCriteriaBuilder
{
    /** @var SearchCriteriaInterfaceFactory */
    private $searchCriteriaFactory;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var FilterGroupBuilder */
    private $filterGroupBuilder;

    /**
     * @param SearchCriteriaInterfaceFactory $searchCriteriaFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        SearchCriteriaInterfaceFactory $searchCriteriaFactory,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * Build searchCriteria from search for product collection
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface
     */
    public function build(SearchCriteriaInterface $searchCriteria): SearchCriteriaInterface
    {
        //Create a copy of search criteria without filters to preserve the results from search
        $searchCriteriaForCollection = $this->searchCriteriaFactory->create()
            ->setSortOrders($searchCriteria->getSortOrders())
            ->setPageSize($searchCriteria->getPageSize())
            ->setCurrentPage($searchCriteria->getCurrentPage());

        //Add category id to enable sorting by position
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == CategoryProductLink::KEY_CATEGORY_ID) {
                    $categoryFilter = $this->filterBuilder
                        ->setField(CategoryProductLink::KEY_CATEGORY_ID)
                        ->setValue($filter->getValue())
                        ->setConditionType($filter->getConditionType())
                        ->create();

                    $this->filterGroupBuilder->addFilter($categoryFilter);
                    $categoryGroup = $this->filterGroupBuilder->create();
                    $searchCriteriaForCollection->setFilterGroups([$categoryGroup]);
                }
            }
        }
        return $searchCriteriaForCollection;
    }
}
