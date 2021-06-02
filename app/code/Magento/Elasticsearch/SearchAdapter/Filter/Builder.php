<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Filter;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Request\FilterInterface as RequestFilterInterface;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Elasticsearch\Model\Script;
use Magento\Elasticsearch\SearchAdapter\Field;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\FilterInterface;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Wildcard;
use Magento\Framework\Search\RequestInterface;

/**
 * Class Builder to build Elasticsearch filter
 */
class Builder implements BuilderInterface
{
    /**
     * @var FilterInterface[]
     */
    private $filters;

    /**
     * @var string|null
     */
    private $requestName = null;

    /**
     * @var Script\BuilderInterface
     */
    private $scriptBuilder;

    /**
     * @var Field\ScriptResolverPoolInterface
     */
    private $fieldScriptResolverPool;

    /**
     * @param Range $range
     * @param Term $term
     * @param Wildcard $wildcard
     * @param Script\BuilderInterface|null $scriptBuilder
     * @param Field\ScriptResolverPoolInterface|null $fieldScriptResolverPool
     */
    public function __construct(
        Range $range,
        Term $term,
        Wildcard $wildcard,
        ?Script\BuilderInterface $scriptBuilder = null,
        ?Field\ScriptResolverPoolInterface $fieldScriptResolverPool = null
    ) {
        $this->filters = [
            RequestFilterInterface::TYPE_RANGE => $range,
            RequestFilterInterface::TYPE_TERM => $term,
            RequestFilterInterface::TYPE_WILDCARD => $wildcard,
        ];

        $this->scriptBuilder = $scriptBuilder ?? ObjectManager::getInstance()
            ->get(Script\Builder::class);
        $this->fieldScriptResolverPool = $fieldScriptResolverPool ?? ObjectManager::getInstance()
            ->get(Field\ScriptResolverPoolInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function build(RequestFilterInterface $filter, $conditionType)
    {
        return $this->processFilter($filter, $conditionType);
    }

    /**
     * @inheritdoc
     */
    public function buildForRequest(RequestInterface $request, RequestFilterInterface $filter, $conditionType)
    {
        $this->requestName = $request->getName();

        try {
            $result = $this->processFilter($filter, $conditionType);
        } finally {
            $this->requestName = null;
        }

        return $result;
    }

    /**
     * Processes filter object
     *
     * @param RequestFilterInterface $filter
     * @param string $conditionType
     * @return array
     */
    private function processFilter(RequestFilterInterface $filter, $conditionType)
    {
        if (RequestFilterInterface::TYPE_BOOL == $filter->getType()) {
            $query = $this->processBoolFilter($filter, $this->isNegation($conditionType));
        } else {
            if (!array_key_exists($filter->getType(), $this->filters)) {
                throw new \InvalidArgumentException('Unknown filter type ' . $filter->getType());
            }

            $fieldName = !$this->requestName || !is_callable([ $filter, 'getField' ])
                ? null
                : (string) $filter->getField();

            $fieldScriptResolver = !$fieldName
                ? null
                : $this->fieldScriptResolverPool->getFieldScriptResolver($fieldName);

            $fieldScript = !$fieldScriptResolver
                ? null
                : $fieldScriptResolver->getFieldFilterScript($fieldName, $filter, $this->requestName);

            $query = [
                'bool' => [
                    $conditionType => $fieldScript
                        ? [
                            [
                                'script' => [
                                    'script' => $this->scriptBuilder->buildScript($fieldScript),
                                ],
                            ],
                        ]
                        : $this->filters[$filter->getType()]->buildFilter($filter),
                ],
            ];
        }

        return $query;
    }

    /**
     * Processes filter
     *
     * @param RequestFilterInterface|BoolExpression $filter
     * @param bool $isNegation
     * @return array
     */
    protected function processBoolFilter(RequestFilterInterface $filter, $isNegation)
    {
        $must = $this->buildFilters(
            $filter->getMust(),
            $this->mapConditionType(BuilderInterface::FILTER_QUERY_CONDITION_MUST, $isNegation)
        );
        $should = $this->buildFilters($filter->getShould(), BuilderInterface::FILTER_QUERY_CONDITION_SHOULD);
        $mustNot = $this->buildFilters(
            $filter->getMustNot(),
            $this->mapConditionType(BuilderInterface::FILTER_QUERY_CONDITION_MUST_NOT, $isNegation)
        );

        $queries = [
            'bool' => array_merge(
                isset($must['bool']) ? $must['bool'] : [],
                isset($should['bool']) ? $should['bool'] : [],
                isset($mustNot['bool']) ? $mustNot['bool'] : []
            ),
        ];

        return $queries;
    }

    /**
     * Build filters
     *
     * @param RequestFilterInterface[] $filters
     * @param string $conditionType
     * @return string
     */
    private function buildFilters(array $filters, $conditionType)
    {
        $queries = [];
        foreach ($filters as $filter) {
            $filterQuery = $this->processFilter($filter, $conditionType);
            if (isset($filterQuery['bool'][$conditionType])) {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $queries['bool'][$conditionType] = array_merge(
                    isset($queries['bool'][$conditionType]) ? $queries['bool'][$conditionType] : [],
                    $filterQuery['bool'][$conditionType]
                );
            }
        }

        return $queries;
    }

    /**
     * Returns is condition type navigation
     *
     * @param string $conditionType
     * @return bool
     */
    private function isNegation($conditionType)
    {
        return BuilderInterface::FILTER_QUERY_CONDITION_MUST_NOT === $conditionType;
    }

    /**
     * Maps condition type
     *
     * @param string $conditionType
     * @param bool $isNegation
     * @return string
     */
    private function mapConditionType($conditionType, $isNegation)
    {
        if ($isNegation) {
            if ($conditionType == BuilderInterface::FILTER_QUERY_CONDITION_MUST) {
                $conditionType = BuilderInterface::FILTER_QUERY_CONDITION_MUST_NOT;
            } elseif ($conditionType == BuilderInterface::FILTER_QUERY_CONDITION_MUST_NOT) {
                $conditionType = BuilderInterface::FILTER_QUERY_CONDITION_MUST;
            }
        }
        return $conditionType;
    }
}
