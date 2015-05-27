<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\Resource;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder;
use Magento\Framework\Search\Adapter\Mysql\Query\MatchContainer;
use Magento\Framework\Search\Adapter\Mysql\Query\MatchContainerFactory;
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
     * @var Dimensions
     */
    private $dimensionsBuilder;

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
     * @var MatchContainerFactory
     */
    private $matchContainerFactory;

    /**
     * @param ScoreBuilderFactory $scoreBuilderFactory
     * @param MatchQueryBuilder $matchQueryBuilder
     * @param Builder $filterBuilder
     * @param Dimensions $dimensionsBuilder
     * @param ConditionManager $conditionManager
     * @param Resource|Resource $resource
     * @param EntityMetadata $entityMetadata
     * @param MatchContainerFactory $matchContainerFactory
     * @param IndexBuilderInterface[] $indexProviders
     */
    public function __construct(
        ScoreBuilderFactory $scoreBuilderFactory,
        Builder $filterBuilder,
        Dimensions $dimensionsBuilder,
        ConditionManager $conditionManager,
        Resource $resource,
        EntityMetadata $entityMetadata,
        MatchContainerFactory $matchContainerFactory,
        array $indexProviders
    ) {
        $this->scoreBuilderFactory = $scoreBuilderFactory;
        $this->filterBuilder = $filterBuilder;
        $this->dimensionsBuilder = $dimensionsBuilder;
        $this->conditionManager = $conditionManager;
        $this->resource = $resource;
        $this->entityMetadata = $entityMetadata;
        $this->indexProviders = $indexProviders;
        $this->matchContainerFactory = $matchContainerFactory;
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

        $matchContainer = $this->matchContainerFactory->create(
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
            $matchContainer
        );
        $select = $this->processDimensions($request, $select);
        $select->columns($scoreBuilder->build());
        $select->limit($request->getSize());

        $select = $this->createAroundSelect($select, $scoreBuilder);

        $matchQueries = $matchContainer->getQueries();

        if ($matchQueries) {
            $subSelect = $select;
            $select = $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE)->select();
            $tables = array_merge($matchContainer->getQueryNames(), ['main_select.relevance']);
            $relevance = implode('.relevance + ', $tables);
            $select
                ->from(
                    ['main_select' => $subSelect],
                    [
                        $this->entityMetadata->getEntityId() => 'entity_id',
                        'relevance' => sprintf('(%s)', $relevance),
                    ]
                );

            foreach ($matchQueries as $matchName => $matchSelect) {
                $select->join(
                    [$matchName => $this->createAroundSelect($matchSelect, $scoreBuilder)],
                    $matchName . '.entity_id = main_select.entity_id',
                    []
                );
            }
        }

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
                    $this->entityMetadata->getEntityId() => 'product_id',
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
     * @param MatchContainer $matchContainer
     * @return Select
     * @throws \InvalidArgumentException
     */
    protected function processQuery(
        ScoreBuilder $scoreBuilder,
        RequestQueryInterface $query,
        Select $select,
        $conditionType,
        MatchContainer $matchContainer
    ) {
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_MATCH:
                /** @var MatchQuery $query */
                $select = $matchContainer->build(
                    $scoreBuilder,
                    $select,
                    $query,
                    $conditionType
                );
                break;
            case RequestQueryInterface::TYPE_BOOL:
                /** @var BoolQuery $query */
                $select = $this->processBoolQuery($scoreBuilder, $query, $select, $matchContainer);
                break;
            case RequestQueryInterface::TYPE_FILTER:
                /** @var FilterQuery $query */
                $select = $this->processFilterQuery($scoreBuilder, $query, $select, $conditionType, $matchContainer);
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
     * @param MatchContainer $matchContainer
     * @return Select
     */
    private function processBoolQuery(
        ScoreBuilder $scoreBuilder,
        BoolQuery $query,
        Select $select,
        MatchContainer $matchContainer
    ) {
        $scoreBuilder->startQuery();

        $select = $this->processBoolQueryCondition(
            $scoreBuilder,
            $query->getMust(),
            $select,
            BoolQuery::QUERY_CONDITION_MUST,
            $matchContainer
        );

        $select = $this->processBoolQueryCondition(
            $scoreBuilder,
            $query->getShould(),
            $select,
            BoolQuery::QUERY_CONDITION_SHOULD,
            $matchContainer
        );

        $select = $this->processBoolQueryCondition(
            $scoreBuilder,
            $query->getMustNot(),
            $select,
            BoolQuery::QUERY_CONDITION_NOT,
            $matchContainer
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
     * @param MatchContainer $matchContainer
     * @return Select
     */
    private function processBoolQueryCondition(
        ScoreBuilder $scoreBuilder,
        array $subQueryList,
        Select $select,
        $conditionType,
        MatchContainer $matchContainer
    ) {
        foreach ($subQueryList as $subQuery) {
            $select = $this->processQuery($scoreBuilder, $subQuery, $select, $conditionType, $matchContainer);
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
     * @param MatchContainer $matchContainer
     * @return Select
     */
    private function processFilterQuery(
        ScoreBuilder $scoreBuilder,
        FilterQuery $query,
        Select $select,
        $conditionType,
        MatchContainer $matchContainer
    ) {
        $scoreBuilder->startQuery();
        switch ($query->getReferenceType()) {
            case FilterQuery::REFERENCE_QUERY:
                $select = $this->processQuery(
                    $scoreBuilder,
                    $query->getReference(),
                    $select,
                    $conditionType,
                    $matchContainer
                );
                $scoreBuilder->endQuery($query->getBoost());
                break;
            case FilterQuery::REFERENCE_FILTER:
                $filterCondition = $this->filterBuilder->build($query->getReference(), $conditionType);
                $select->where($filterCondition);
                break;
        }
        $scoreBuilder->endQuery($query->getBoost());
        return $select;
    }

    /**
     * Add filtering by dimensions
     *
     * @param RequestInterface $request
     * @param Select $select
     * @return \Magento\Framework\DB\Select
     */
    private function processDimensions(RequestInterface $request, Select $select)
    {
        $dimensions = [];
        foreach ($request->getDimensions() as $dimension) {
            $dimensions[] = $this->dimensionsBuilder->build($dimension);
        }

        $query = $this->conditionManager->combineQueries($dimensions, Select::SQL_OR);
        if (!empty($query)) {
            $select->where($this->conditionManager->wrapBrackets($query));
        }

        return $select;
    }
}
