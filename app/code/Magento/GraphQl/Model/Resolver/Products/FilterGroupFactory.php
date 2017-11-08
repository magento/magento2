<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Resolver\Products;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\GraphQl\Model\GraphQl\Clause;
use Magento\GraphQl\Model\GraphQl\Connective;
use Magento\GraphQl\Model\GraphQl\Operator;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;

/**
 * Class FilterGroupFactory
 */
class FilterGroupFactory
{
    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var FilterGroupBuilder */
    private $filterGroupBuilder;

    /** @var ProductFilterResolver */
    private $filterResolver;

    /**
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\GraphQl\Model\Resolver\Products\ProductFilterResolver $filterResolver
     */
    public function __construct(
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\GraphQl\Model\Resolver\Products\ProductFilterResolver $filterResolver
    ) {
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterResolver = $filterResolver;
    }

    /**
     * Create a filter groups from an AST
     *
     * @param ResolveInfo $info
     * @return \Magento\Framework\Api\Search\FilterGroup[]
     * @throws \GraphQL\Error\Error
     */
    public function create(ResolveInfo $info)
    {
        if (count($info->fieldNodes) !== 1) {
            // TODO: support multiple endpoints at the same time
            throw new \GraphQL\Error\Error('Multiple nodes at top level are not supported');
        }

        /** @var \GraphQL\Language\AST\FieldNode $fieldNode */
        $fieldNode = current($info->fieldNodes);

        /** @var Connective $filters */
        $filters = $this->filterResolver->getFilterFromAst('product', $fieldNode);

        /** @var \Magento\Framework\Api\Search\FilterGroup[] $searchCriteriaFilterGroups */
        $searchCriteriaFilterGroups = [];

        //Requiring and as top level operator
        $filters = current($filters->getConditions());

        if (!$filters instanceof Connective) {
            throw new \GraphQL\Error\Error('Top level has to have condition operator');
        }

        foreach ($filters->getConditions() as $node) {
            if ($node instanceof Operator) {
                throw new \GraphQL\Error\Error('Can\'t support nested operators');
            }

            if ($node instanceof Clause) {
                $searchCriteriaFilterGroups[] = $this->processClause($node);
            } elseif ($node instanceof Connective) {
                $searchCriteriaFilterGroups[] = $this->processConnective($node);
            } else {
                throw new \GraphQL\Error\Error('Nesting "OR" node type not supported');
            }
        }

        return $searchCriteriaFilterGroups;
    }

    /**
     * Process an AST Connective
     *
     * @param Connective $connective
     * @return \Magento\Framework\Api\Search\FilterGroup
     * @throws \GraphQL\Error\Error
     */
    private function processConnective(Connective $connective)
    {
        //process sub condition
        foreach ($connective->getConditions() as $subNode) {
            if ($subNode instanceof Clause) {
                $subFilter = $this->filterBuilder
                    ->setField($subNode->getFieldName())
                    ->setValue($subNode->getClauseValue())// Not strictly needed for "null" and "notnull"
                    ->setConditionType($subNode->getClauseType())
                    ->create();

                $this->filterGroupBuilder->addFilter($subFilter);
            } else {
                throw new \GraphQL\Error\Error('Sub nesting nodes not supported');
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
