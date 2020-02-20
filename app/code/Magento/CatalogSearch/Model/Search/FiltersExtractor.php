<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Search;

use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Filter\BoolExpression;

/**
 * Extracts filters from QueryInterface
 *
 * @deprecated Use Magento\Elasticsearch implementation of QueryInterface
 * @see \Magento\Elasticsearch
 */
class FiltersExtractor
{
    /**
     * Extracts filters from QueryInterface
     *
     * @param QueryInterface $query
     * @return FilterInterface[]
     */
    public function extractFiltersFromQuery(QueryInterface $query)
    {
        $filters = [[]];

        switch ($query->getType()) {
            case QueryInterface::TYPE_BOOL:
                /** @var \Magento\Framework\Search\Request\Query\BoolExpression $query */
                foreach ($query->getMust() as $subQuery) {
                    $filters[] = $this->extractFiltersFromQuery($subQuery);
                }
                foreach ($query->getShould() as $subQuery) {
                    $filters[] = $this->extractFiltersFromQuery($subQuery);
                }
                foreach ($query->getMustNot() as $subQuery) {
                    $filters[] = $this->extractFiltersFromQuery($subQuery);
                }
                break;

            case QueryInterface::TYPE_FILTER:
                /** @var Filter $query */
                $filter = $query->getReference();
                if (FilterInterface::TYPE_BOOL === $filter->getType()) {
                    $filters[] = $this->getFiltersFromBoolFilter($filter);
                } else {
                    $filters[] = [$filter];
                }
                break;

            default:
                break;
        }

        return array_merge(...$filters);
    }

    /**
     * Returns list of filters from Bool filter
     *
     * @param BoolExpression $boolExpression
     * @return FilterInterface[]
     */
    private function getFiltersFromBoolFilter(BoolExpression $boolExpression)
    {
        $filters = [[]];

        /** @var BoolExpression $filter */
        foreach ($boolExpression->getMust() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters[] = $this->getFiltersFromBoolFilter($filter);
            } else {
                $filters[] = [$filter];
            }
        }
        foreach ($boolExpression->getShould() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters[] = $this->getFiltersFromBoolFilter($filter);
            } else {
                $filters[] = [$filter];
            }
        }
        foreach ($boolExpression->getMustNot() as $filter) {
            if ($filter->getType() === FilterInterface::TYPE_BOOL) {
                $filters[] = $this->getFiltersFromBoolFilter($filter);
            } else {
                $filters[] = [$filter];
            }
        }
        return array_merge(...$filters);
    }
}
