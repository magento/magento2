<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface
    as FieldNameResolver;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\RequestInterface;

class Position implements ExpressionBuilderInterface
{
    /**
     * @var FieldNameResolver
     */
    private $fieldNameResolver;

    /**
     * @param FieldNameResolver $fieldNameResolver
     */
    public function __construct(FieldNameResolver $fieldNameResolver)
    {
        $this->fieldNameResolver = $fieldNameResolver;
    }

    /**
     * @inheritdoc
     */
    public function build(AttributeAdapter $attribute, string $direction, RequestInterface $request): array
    {
        $sortParams = ['order' => $direction];

        $categoryIds = $this->getCategoryIdsFromQuery($request->getQuery());
        if (count($categoryIds) > 1) {
            $fieldNames = [];
            foreach ($categoryIds as $categoryId) {
                $fieldNames[] = $this->fieldNameResolver->getFieldName($attribute, ['categoryId' => $categoryId]);
            }
            $fieldName = '_script';
            $sortParams += [
                'type' => 'number',
                'script' => [
                    'lang' => 'painless',
                    'source' => <<<SCRIPT
                        long minPos = Long.MAX_VALUE;
                        for (int i = 0; i < params.sortFieldNames.length; ++i) {
                          if (doc[params.sortFieldNames[i]].size() !== 0
                                && doc[params.sortFieldNames[i]].value < minPos
                          ) {
                              minPos = doc[params.sortFieldNames[i]].value;
                          }
                        }
                        return minPos;
                    SCRIPT,
                    'params' => [
                        'sortFieldNames' => $fieldNames,
                    ]
                ],
            ];
        } elseif (!empty($categoryIds)) {
            $categoryId = array_shift($categoryIds);
            $fieldName = $this->fieldNameResolver->getFieldName($attribute, ['categoryId' => $categoryId]);
        } else {
            $fieldName = $this->fieldNameResolver->getFieldName($attribute);
        }

        return [$fieldName => $sortParams];
    }

    /**
     * Get Category Ids from search query.
     *
     * Get Category Ids from Must and Should search queries.
     *
     * @param QueryInterface $queryExpression
     * @return array
     */
    private function getCategoryIdsFromQuery(QueryInterface $queryExpression): array
    {
        $categoryIds = [];
        if ($queryExpression->getType() === QueryInterface::TYPE_BOOL) {
            $queryFilters = $queryExpression->getMust();
            if (is_array($queryFilters) && isset($queryFilters['category'])) {
                $categoryIds = (array) $queryFilters['category']->getReference()->getValue();
            }
        }

        return $categoryIds;
    }
}
