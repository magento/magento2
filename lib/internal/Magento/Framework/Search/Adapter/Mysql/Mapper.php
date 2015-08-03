<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder;
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match;
use Magento\Framework\Search\Adapter\Mysql\Query\MatchContainer;
use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer;
use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainerFactory;
use Magento\Framework\Search\EntityMetadata;
use Magento\Framework\Search\Request\Query\Bool as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * Mapper class. Maps library request to specific adapter dependent query
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mapper
{
    /**
     * @var ScoreBuilder
     */
    private $scoreBuilderFactory;

    /**
     * @var Filter\Builder
     */
    private $filterBuilder;

    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @var IndexBuilderInterface[]
     */
    private $indexProviders;

    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var EntityMetadata
     */
    private $entityMetadata;

    /**
     * @var QueryContainerFactory
     */
    private $queryContainerFactory;
    /**
     * @var Query\Builder\Match
     */
    private $matchBuilder;

    /**
     * @param ScoreBuilderFactory $scoreBuilderFactory
     * @param Builder $filterBuilder
     * @param ConditionManager $conditionManager
     * @param Resource|Resource $resource
     * @param EntityMetadata $entityMetadata
     * @param QueryContainerFactory $queryContainerFactory
     * @param Query\Builder\Match $matchBuilder
     * @param IndexBuilderInterface[] $indexProviders
     */
    public function __construct(
        ScoreBuilderFactory $scoreBuilderFactory,
        Builder $filterBuilder,
        ConditionManager $conditionManager,
        Resource $resource,
        EntityMetadata $entityMetadata,
        QueryContainerFactory $queryContainerFactory,
        Match $matchBuilder,
        array $indexProviders
    ) {
        $this->scoreBuilderFactory = $scoreBuilderFactory;
        $this->filterBuilder = $filterBuilder;
        $this->conditionManager = $conditionManager;
        $this->resource = $resource;
        $this->entityMetadata = $entityMetadata;
        $this->indexProviders = $indexProviders;
        $this->queryContainerFactory = $queryContainerFactory;
        $this->matchBuilder = $matchBuilder;
    }

    /**
     * Build adapter dependent query
     *
     * @param RequestInterface $request
     * @throws \Exception
     * @return Select
     */
    public function buildQuery(RequestInterface $request)
    {
        if (!isset($this->indexProviders[$request->getIndex()])) {
            throw new \Exception('Index provider not configured');
        }

        $indexBuilder = $this->indexProviders[$request->getIndex()];

        $queryContainer = $this->queryContainerFactory->create(
            [
                'indexBuilder' => $indexBuilder,
                'request' => $request
            ]
        );
        $select = $indexBuilder->build($request);
        /** @var ScoreBuilder $scoreBuilder */
        $scoreBuilder = $this->scoreBuilderFactory->create();
        $select = $this->processQuery(
            $scoreBuilder,
            $request->getQuery(),
            $select,
            BoolQuery::QUERY_CONDITION_MUST,
            $queryContainer
        );

        $filtersCount = $queryContainer->getFiltersCount();
        if ($filtersCount > 1) {
            $select->group('entity_id');
            $select->having('COUNT(DISTINCT search_index.attribute_id) = ' . $filtersCount);
        }

        $select = $this->addMatchQueries(
            $request,
            $queryContainer->getDerivedQueries(),
            $scoreBuilder,
            $select,
            $indexBuilder
        );

        $select->limit($request->getSize());
        $select->order('relevance ' . Select::SQL_DESC);
        return $select;
    }

    /**
     * @param Select $select
     * @param ScoreBuilder $scoreBuilder
     * @return Select
     */
    private function createAroundSelect(
        Select $select,
        ScoreBuilder $scoreBuilder
    ) {
        $parentSelect = $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE)->select();
        $parentSelect
            ->from(
                ['main_select' => $select],
                [
                    $this->entityMetadata->getEntityId() => 'entity_id',
                    'relevance' => sprintf('MAX(%s)', $scoreBuilder->getScoreAlias())
                ]
            )
            ->group($this->entityMetadata->getEntityId());
        return $parentSelect;
    }

    /**
     * Process query
     *
     * @param ScoreBuilder $scoreBuilder
     * @param RequestQueryInterface $query
     * @param Select $select
     * @param string $conditionType
     * @param QueryContainer $queryContainer
     * @return Select
     * @throws \InvalidArgumentException
     */
    protected function processQuery(
        ScoreBuilder $scoreBuilder,
        RequestQueryInterface $query,
        Select $select,
        $conditionType,
        QueryContainer $queryContainer
    ) {
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_MATCH:
                /** @var MatchQuery $query */
                $select = $queryContainer->addMatchQuery(
                    $select,
                    $query,
                    $conditionType
                );
                break;
            case RequestQueryInterface::TYPE_BOOL:
                /** @var BoolQuery $query */
                $select = $this->processBoolQuery($scoreBuilder, $query, $select, $queryContainer);
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var FilterQuery $query */
                $select = $this->processFilterQuery($scoreBuilder, $query, $select, $conditionType, $queryContainer);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown query type \'%s\'', $query->getType()));
        }
        return $select;
    }

    /**
     * Process bool query
     *
     * @param ScoreBuilder $scoreBuilder
     * @param BoolQuery $query
     * @param Select $select
     * @param QueryContainer $queryContainer
     * @return Select
     */
    private function processBoolQuery(
        ScoreBuilder $scoreBuilder,
        BoolQuery $query,
        Select $select,
        QueryContainer $queryContainer
    ) {
        $scoreBuilder->startQuery();

        $select = $this->processBoolQueryCondition(
            $scoreBuilder,
            $query->getMust(),
            $select,
            BoolQuery::QUERY_CONDITION_MUST,
            $queryContainer
        );

        $select = $this->processBoolQueryCondition(
            $scoreBuilder,
            $query->getShould(),
            $select,
            BoolQuery::QUERY_CONDITION_SHOULD,
            $queryContainer
        );

        $select = $this->processBoolQueryCondition(
            $scoreBuilder,
            $query->getMustNot(),
            $select,
            BoolQuery::QUERY_CONDITION_NOT,
            $queryContainer
        );

        $scoreBuilder->endQuery($query->getBoost());

        return $select;
    }

    /**
     * Process bool query condition (must, should, must_not)
     *
     * @param ScoreBuilder $scoreBuilder
     * @param RequestQueryInterface[] $subQueryList
     * @param Select $select
     * @param string $conditionType
     * @param QueryContainer $queryContainer
     * @return Select
     */
    private function processBoolQueryCondition(
        ScoreBuilder $scoreBuilder,
        array $subQueryList,
        Select $select,
        $conditionType,
        QueryContainer $queryContainer
    ) {
        foreach ($subQueryList as $subQuery) {
            $select = $this->processQuery($scoreBuilder, $subQuery, $select, $conditionType, $queryContainer);
        }
        $filters = $queryContainer->getFilters();
        if ($filters) {
            $select->where('(' . implode(' OR ', $filters) . ')');
            $queryContainer->clearFilters();
        }
        return $select;
    }

    /**
     * Process filter query
     *
     * @param ScoreBuilder $scoreBuilder
     * @param FilterQuery $query
     * @param Select $select
     * @param string $conditionType
     * @param QueryContainer $queryContainer
     * @return Select
     */
    private function processFilterQuery(
        ScoreBuilder $scoreBuilder,
        FilterQuery $query,
        Select $select,
        $conditionType,
        QueryContainer $queryContainer
    ) {
        $scoreBuilder->startQuery();
        switch ($query->getReferenceType()) {
            case FilterQuery::REFERENCE_QUERY:
                $select = $this->processQuery(
                    $scoreBuilder,
                    $query->getReference(),
                    $select,
                    $conditionType,
                    $queryContainer
                );
                $scoreBuilder->endQuery($query->getBoost());
                break;
            case FilterQuery::REFERENCE_FILTER:
                $filterCondition = $this->filterBuilder->build($query->getReference(), $conditionType, $queryContainer);
                if ($filterCondition) {
                    $select->where($filterCondition);
                }
                break;
        }
        $scoreBuilder->endQuery($query->getBoost());
        return $select;
    }

    /**
     * @param RequestInterface $request
     * @param MatchContainer[] $matchQueries
     * @param ScoreBuilder $scoreBuilder
     * @param Select $select
     * @param IndexBuilderInterface $indexBuilder
     * @return Select
     * @internal param QueryContainer $queryContainer
     */
    private function addMatchQueries(
        RequestInterface $request,
        array $matchQueries,
        ScoreBuilder $scoreBuilder,
        Select $select,
        IndexBuilderInterface $indexBuilder
    ) {
        if (!$matchQueries) {
            $select->columns($scoreBuilder->build());
            $select = $this->createAroundSelect($select, $scoreBuilder);
        } elseif (count($matchQueries) === 1) {
            $matchContainer = reset($matchQueries);
            $this->matchBuilder->build(
                $scoreBuilder,
                $select,
                $matchContainer->getRequest(),
                $matchContainer->getConditionType()
            );
            $select->columns($scoreBuilder->build());
            $select = $this->createAroundSelect($select, $scoreBuilder);
        } elseif (count($matchQueries) > 1) {
            $select->columns($scoreBuilder->build());
            $select = $this->createAroundSelect($select, $scoreBuilder);
            $subSelect = $select;
            $select = $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE)->select();
            $tables = array_merge(array_keys($matchQueries), ['main_select.relevance']);
            $relevance = implode('.relevance + ', $tables);
            $select
                ->from(
                    ['main_select' => $subSelect],
                    [
                        $this->entityMetadata->getEntityId() => 'entity_id',
                        'relevance' => sprintf('(%s)', $relevance),
                    ]
                );

            foreach ($matchQueries as $matchName => $matchContainer) {
                $matchSelect = $indexBuilder->build($request);
                $matchScoreBuilder = $this->scoreBuilderFactory->create();
                $matchSelect = $this->matchBuilder->build(
                    $matchScoreBuilder,
                    $matchSelect,
                    $matchContainer->getRequest(),
                    $matchContainer->getConditionType()
                );
                $matchSelect->columns($matchScoreBuilder->build());
                $select->join(
                    [$matchName => $this->createAroundSelect($matchSelect, $scoreBuilder)],
                    $matchName . '.entity_id = main_select.entity_id',
                    []
                );
            }
        }
        return $select;
    }
}
