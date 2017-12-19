<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\SearchCriteria\ArgumentApplier;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\GraphQl\Argument\SearchCriteria\ArgumentApplierInterface;
use Magento\Framework\GraphQl\ArgumentInterface;

class Search implements ArgumentApplierInterface
{
    const ARGUMENT_NAME = 'search';

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(FilterBuilder $filterBuilder, FilterGroupBuilder $filterGroupBuilder)
    {
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function applyArgument(SearchCriteriaInterface $searchCriteria, ArgumentInterface $argument)
    {
        $searchTerm = $argument->getValue();
        $searchTermFilter = $this->filterBuilder->setField('search_term')->setValue($searchTerm)->create();
        $this->filterGroupBuilder->addFilter($searchTermFilter);
        $filterGroups = $searchCriteria->getFilterGroups();
        $filterGroups[] = $this->filterGroupBuilder->create();
        $searchCriteria->setFilterGroups($filterGroups);
        $searchCriteria->setRequestName('quick_search_container');
        return $searchCriteria;
    }
}
