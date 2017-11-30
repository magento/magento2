<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\SearchCriteria;

use Magento\Framework\GraphQl\Argument\Find\Clause;
use Magento\Framework\GraphQl\Argument\Find\Connective;
use Magento\Framework\GraphQl\Argument\Find\Operator;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\GraphQl\Argument\Find\FindArgumentValueInterface;
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
     * @param FindArgumentValueInterface $arguments
     * @return \Magento\Framework\Api\Search\FilterGroup[]
     * @throws GraphQlInputException
     */
    public function create($arguments)
    {
        /** @var Connective $filters */
        $filters = $arguments->getClauseList();
        /** @var \Magento\Framework\Api\Search\FilterGroup[] $searchCriteriaFilterGroups */
        $searchCriteriaFilterGroups = [];

        //Requiring and as top level operator
        $filters = current($filters->getConditions());

        if (!$filters instanceof Connective) {
            throw new GraphQlInputException(new Phrase('Top level has to have condition operator'));
        }

        foreach ($filters->getConditions() as $node) {
            if ($node instanceof Operator) {
                throw new GraphQlInputException(new Phrase('Can\'t support nested operators'));
            }

            if ($node instanceof Clause) {
                $searchCriteriaFilterGroups[] = $this->processClause($node);
            } elseif ($node instanceof Connective) {
                $searchCriteriaFilterGroups[] = $this->processConnective($node);
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
            } else {
                throw new GraphQlInputException(new Phrase('Sub nesting nodes not supported'));
            }
        }
        return $this->filterGroupBuilder->create();
    }

    /**
     * Process an AST Clause
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
