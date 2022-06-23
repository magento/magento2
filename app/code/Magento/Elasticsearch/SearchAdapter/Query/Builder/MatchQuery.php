<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface as TypeResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformerPool;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;

/**
 * Builder for match query
 */
class MatchQuery implements QueryInterface
{
    /**
     * Elasticsearch condition for case when query must not appear in the matching documents.
     */
    public const QUERY_CONDITION_MUST_NOT = 'must_not';

    /**
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /**
     * @var AttributeProvider
     */
    private $attributeProvider;

    /**
     * @var TypeResolver
     */
    private $fieldTypeResolver;

    /**
     * @var ValueTransformerPool
     */
    private $valueTransformerPool;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param FieldMapperInterface $fieldMapper
     * @param AttributeProvider $attributeProvider
     * @param TypeResolver $fieldTypeResolver
     * @param ValueTransformerPool $valueTransformerPool
     * @param Config $config
     */
    public function __construct(
        FieldMapperInterface $fieldMapper,
        AttributeProvider $attributeProvider,
        TypeResolver $fieldTypeResolver,
        ValueTransformerPool $valueTransformerPool,
        Config $config
    ) {
        $this->fieldMapper = $fieldMapper;
        $this->attributeProvider = $attributeProvider;
        $this->fieldTypeResolver = $fieldTypeResolver;
        $this->valueTransformerPool = $valueTransformerPool;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $selectQuery, RequestQueryInterface $requestQuery, $conditionType)
    {
        $queryValue = $this->prepareQuery($requestQuery->getValue(), $conditionType);
        $queries = $this->buildQueries($requestQuery->getMatches(), $queryValue);
        $requestQueryBoost = $requestQuery->getBoost() ?: 1;
        $minimumShouldMatch = $this->config->getElasticsearchConfigData('minimum_should_match');

        foreach ($queries as $query) {
            $queryBody = $query['body'];
            $matchKey = array_keys($queryBody)[0];
            foreach ($queryBody[$matchKey] as $field => $matchQuery) {
                $matchQuery['boost'] = $requestQueryBoost + $matchQuery['boost'];
                if ($minimumShouldMatch && $matchKey != 'match_phrase_prefix') {
                    $matchQuery['minimum_should_match'] = $minimumShouldMatch;
                }
                $queryBody[$matchKey][$field] = $matchQuery;
            }
            $selectQuery['bool'][$query['condition']][] = $queryBody;
        }

        return $selectQuery;
    }

    /**
     * Prepare query
     *
     * @param string $queryValue
     * @param string $conditionType
     * @return array
     */
    private function prepareQuery(string $queryValue, string $conditionType): array
    {
        $condition = $conditionType === BoolExpression::QUERY_CONDITION_NOT
            ? self::QUERY_CONDITION_MUST_NOT
            : $conditionType;

        return [
            'condition' => $condition,
            'value' => $queryValue,
        ];
    }

    /**
     * Creates valid ElasticSearch search conditions from Match queries
     *
     * The purpose of this method is to create a structure which represents valid search query
     * for a full-text search.
     * It sets search query condition, the search query itself, and sets the search query boost.
     *
     * The search query boost is an optional in the search query and therefore it will be set to 1 by default
     * if none passed with a match query.
     *
     * @param array $matches
     * @param array $queryValue
     * @return array
     */
    private function buildQueries(array $matches, array $queryValue): array
    {
        $conditions = [];

        // Checking for quoted phrase \"phrase test\", trim escaped surrounding quotes if found
        $count = 0;
        $value = preg_replace('#^"(.*)"$#m', '$1', $queryValue['value'], -1, $count);
        $condition = ($count) ? 'match_phrase' : 'match';
        $transformedTypes = [];

        foreach ($matches as $match) {
            $resolvedField = $this->fieldMapper->getFieldName(
                $match['field'],
                ['type' => FieldMapperInterface::TYPE_QUERY]
            );
            $attributeAdapter = $this->attributeProvider->getByAttributeCode($resolvedField);
            $fieldType = $this->fieldTypeResolver->getFieldType($attributeAdapter);
            $valueTransformer = $this->valueTransformerPool->get($fieldType ?? 'text');
            $valueTransformerHash = \spl_object_hash($valueTransformer);

            if (!isset($transformedTypes[$valueTransformerHash])) {
                $transformedTypes[$valueTransformerHash] = $valueTransformer->transform($value);
            }
            $transformedValue = $transformedTypes[$valueTransformerHash];

            if (null === $transformedValue) {
                //Value is incompatible with this field type.
                continue;
            }
            $matchCondition = $match['matchCondition'] ?? $condition;
            $fields = [];
            $fields[$resolvedField] = [
                'query' => $transformedValue,
                'boost' => $match['boost'] ?? 1,
            ];

            if (isset($match['analyzer'])) {
                $fields[$resolvedField]['analyzer'] = $match['analyzer'];
            }
            $conditions[] = [
                'condition' => $queryValue['condition'],
                'body' => [
                    $matchCondition => $fields,
                ],
            ];
        }

        return $conditions;
    }
}
