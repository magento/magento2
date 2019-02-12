<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\SearchAdapter\Query\ValueTransformerPool;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;

/**
 * Builder for match query.
 */
class Match implements QueryInterface
{
    /**
     * Elasticsearch condition for case when query must not appear in the matching documents.
     */
    const QUERY_CONDITION_MUST_NOT = 'must_not';

    /**
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /**
     * @deprecated
     * @see \Magento\Elasticsearch\SearchAdapter\Query\ValueTransformer\TextTransformer
     * @var PreprocessorInterface[]
     */
    protected $preprocessorContainer;

    /**
     * @var ValueTransformerPool
     */
    private $valueTransformerPool;

    /**
     * @param FieldMapperInterface $fieldMapper
     * @param PreprocessorInterface[] $preprocessorContainer
     * @param ValueTransformerPool|null $valueTransformerPool
     */
    public function __construct(
        FieldMapperInterface $fieldMapper,
        array $preprocessorContainer,
        ValueTransformerPool $valueTransformerPool = null
    ) {
        $this->fieldMapper = $fieldMapper;
        $this->preprocessorContainer = $preprocessorContainer;
        $this->valueTransformerPool = $valueTransformerPool ?? ObjectManager::getInstance()
            ->get(ValueTransformerPool::class);
    }

    /**
     * @inheritdoc
     */
    public function build(array $selectQuery, RequestQueryInterface $requestQuery, $conditionType)
    {
        $queryValue = $this->prepareQuery($requestQuery->getValue(), $conditionType);
        $queries = $this->buildQueries($requestQuery->getMatches(), $queryValue);
        $requestQueryBoost = $requestQuery->getBoost() ?: 1;
        foreach ($queries as $query) {
            $queryBody = $query['body'];
            $matchKey = isset($queryBody['match_phrase']) ? 'match_phrase' : 'match';
            foreach ($queryBody[$matchKey] as $field => $matchQuery) {
                $matchQuery['boost'] = $requestQueryBoost + $matchQuery['boost'];
                $queryBody[$matchKey][$field] = $matchQuery;
            }
            $selectQuery['bool'][$query['condition']][] = $queryBody;
        }

        return $selectQuery;
    }

    /**
     * Prepare query.
     *
     * @param string $queryValue
     * @param string $conditionType
     * @return array
     */
    protected function prepareQuery($queryValue, $conditionType)
    {
        $condition = $conditionType === BoolExpression::QUERY_CONDITION_NOT ?
            self::QUERY_CONDITION_MUST_NOT : $conditionType;
        return [
            'condition' => $condition,
            'value' => $queryValue,
        ];
    }

    /**
     * Creates valid ElasticSearch search conditions from Match queries.
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
    protected function buildQueries(array $matches, array $queryValue)
    {
        $conditions = [];

        // Checking for quoted phrase \"phrase test\", trim escaped surrounding quotes if found
        $count = 0;
        $value = preg_replace('#^"(.*)"$#m', '$1', $queryValue['value'], -1, $count);
        $condition = ($count) ? 'match_phrase' : 'match';

        $attributesTypes = $this->fieldMapper->getAllAttributesTypes();
        $transformedTypes = [];
        foreach ($matches as $match) {
            $resolvedField = $this->fieldMapper->getFieldName(
                $match['field'],
                ['type' => FieldMapperInterface::TYPE_QUERY]
            );
            $valueTransformer = $this->valueTransformerPool->get($attributesTypes[$resolvedField]['type'] ?? 'text');
            $valueTransformerHash = \spl_object_hash($valueTransformer);
            if (!isset($transformedTypes[$valueTransformerHash])) {
                $transformedTypes[$valueTransformerHash] = $valueTransformer->transform($value);
            }
            $transformedValue = $transformedTypes[$valueTransformerHash];
            if (null === $transformedValue) {
                //Value is incompatible with this field type.
                continue;
            }

            $conditions[] = [
                'condition' => $queryValue['condition'],
                'body' => [
                    $condition => [
                        $resolvedField => [
                            'query' => $transformedValue,
                            'boost' => $match['boost'] ?? 1,
                        ],
                    ],
                ],
            ];
        }

        return $conditions;
    }

    /**
     * Escape a value for special query characters such as ':', '(', ')', '*', '?', etc.
     *
     * @deprecated
     * @see \Magento\Elasticsearch\SearchAdapter\Query\ValueTransformer\TextTransformer
     * @param string $value
     * @return string
     */
    protected function escape($value)
    {
        $pattern = '/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/';
        $replace = '\\\$1';

        return preg_replace($pattern, $replace, $value);
    }
}
