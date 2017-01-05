<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Query\Builder;

use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Framework\Search\Adapter\Preprocessor\PreprocessorInterface;

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
     * @var PreprocessorInterface[]
     */
    protected $preprocessorContainer;

    /**
     * @param FieldMapperInterface $fieldMapper
     * @param PreprocessorInterface[] $preprocessorContainer
     */
    public function __construct(
        FieldMapperInterface $fieldMapper,
        array $preprocessorContainer
    ) {
        $this->fieldMapper = $fieldMapper;
        $this->preprocessorContainer = $preprocessorContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $selectQuery, RequestQueryInterface $requestQuery, $conditionType)
    {
        $queryValue = $this->prepareQuery($requestQuery->getValue(), $conditionType);
        $queries = $this->buildQueries($requestQuery->getMatches(), $queryValue);
        foreach ($queries as $query) {
            $selectQuery['bool'][$query['condition']][]= $query['body'];
        }
        return $selectQuery;
    }

    /**
     * @param string $queryValue
     * @param string $conditionType
     * @return array
     */
    protected function prepareQuery($queryValue, $conditionType)
    {
        $queryValue = $this->escape($queryValue);
        foreach ($this->preprocessorContainer as $preprocessor) {
            $queryValue = $preprocessor->process($queryValue);
        }
        $condition = $conditionType === BoolExpression::QUERY_CONDITION_NOT ?
            self::QUERY_CONDITION_MUST_NOT : $conditionType;
        return [
            'condition' => $condition,
            'value' => $queryValue,
        ];
    }

    /**
     * @param array $matches
     * @param array $queryValue
     * @return array
     */
    protected function buildQueries($matches, $queryValue)
    {
        $conditions = [];
        foreach ($matches as $match) {
            $resolvedField = $this->fieldMapper->getFieldName(
                $match['field'],
                ['type' => FieldMapperInterface::TYPE_QUERY]
            );
            $conditions[] = [
                'condition' => $queryValue['condition'],
                'body' => [
                    'match' => [
                        $resolvedField => $queryValue['value'],
                    ],
                ],
            ];
        }
        return $conditions;
    }

    /**
     * Escape a value for special query characters such as ':', '(', ')', '*', '?', etc.
     *
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
