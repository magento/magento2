<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\SearchCriteria;

use Magento\Framework\GraphQl\Argument\Filter\Clause;
use Magento\Framework\GraphQl\Argument\Filter\Connective;
use Magento\Framework\GraphQl\Argument\Filter\Operator;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\GraphQl\Argument\Filter\FilterArgumentValueInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Phrase;

/**
 * Class FilterGroupFactory
 */
class FilterGroupFactory
{
    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var FilterGroupBuilder */
    private $filterGroupBuilder;

    /**
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * Create a filter groups from an AST
     *
     * @param FilterArgumentValueInterface $arguments
     * @return \Magento\Framework\Api\Search\FilterGroup[]
     * @throws GraphQlInputException
     */
    public function create($arguments)
    {
        $filters = $arguments->getValue();
        /** @var \Magento\Framework\Api\Search\FilterGroup[] $searchCriteriaFilterGroups */
        $searchCriteriaFilterGroups = [];

        foreach ($filters->getConditions() as $filter) {
            if ($filter instanceof Operator) {
                throw new GraphQlInputException(new Phrase('Can\'t support nested operators'));
            }

            if ($filter instanceof Clause) {
                $searchCriteriaFilterGroups[] = $this->processClause($filter);
            } elseif ($filter instanceof Connective) {
                $searchCriteriaFilterGroups[] = $this->processConnective($filter);
            } else {
                throw new GraphQlInputException(new Phrase('Nesting "OR" node type not supported'));
            }
        }

        return $searchCriteriaFilterGroups;
    }

    /**
     * Process an AST Connective
     *
     * @param Connective $connective
     * @return \Magento\Framework\Api\Search\FilterGroup
     * @throws GraphQlInputException
     */
    private function processConnective(Connective $connective)
    {
        foreach ($connective->getConditions() as $subNode) {
            if ($subNode instanceof Clause) {
                $subFilter = $this->filterBuilder
                    ->setField($subNode->getFieldName())
                    ->setValue($subNode->getClauseValue())// Not strictly needed for "null" and "notnull"
                    ->setConditionType($subNode->getClauseType())
                    ->create();

                $this->filterGroupBuilder->addFilter($subFilter);
            } elseif ($subNode instanceof Connective) {
                // This recursive OR processing can be done because AND is not yet supported
                // we should not be doing this for OR if both AND and OR will be nestedly supported
                // because it's mathematically incorrect to reduce OR in a boolean operation
                // you can only do it when you have only OR operation.
                if (((string)$subNode->getOperator()) == 'OR') {
                    return $this->processConnective($subNode);
                } else {
                    throw new GraphQlInputException(
                        new Phrase('Sub nesting of %1 is not supported', [$subNode->getOperator()])
                    );
                }
            }
        }
        return $this->filterGroupBuilder->create();
    }

    /**
     * Process an AST clause
     *
     * @param Clause $clause
     * @return \Magento\Framework\Api\Search\FilterGroup
     */
    private function processClause(Clause $clause)
    {
        $searchCriteriaFilter = $this->filterBuilder
            ->setField($clause->getFieldName())
            ->setValue($clause->getClauseValue())// Not strictly needed for "null" and "notnull"
            ->setConditionType($clause->getClauseType())
            ->create();

        $this->filterGroupBuilder->addFilter($searchCriteriaFilter);
        return $this->filterGroupBuilder->create();
    }
}
