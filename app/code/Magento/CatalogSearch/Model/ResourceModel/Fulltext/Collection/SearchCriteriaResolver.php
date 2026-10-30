<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;

use Magento\Framework\Data\Collection;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchCriteria;

/**
 * Resolve specific attributes for search criteria.
 */
class SearchCriteriaResolver implements SearchCriteriaResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $builder;

    /**
     * @var string
     */
    private $searchRequestName;

    /**
     * SearchCriteriaResolver constructor.
     * @param SearchCriteriaBuilder $builder
     * @param string $searchRequestName
     */
    public function __construct(
        SearchCriteriaBuilder $builder,
        string $searchRequestName
    ) {
        $this->builder = $builder;
        $this->searchRequestName = $searchRequestName;
    }

    /**
     * @inheritdoc
     */
    public function resolve() : SearchCriteria
    {
        $searchCriteria = $this->builder->create();
        $searchCriteria->setRequestName($this->searchRequestName);

        return $searchCriteria;
    }
}
