<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\Filter\Builder;
use Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match;
use Magento\Framework\Search\Adapter\Mysql\Query\MatchContainer;
use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainer;
use Magento\Framework\Search\Adapter\Mysql\Query\QueryContainerFactory;
use Magento\Framework\Search\EntityMetadata;
use Magento\Framework\Search\Request\Query\BoolExpression as BoolQuery;
use Magento\Framework\Search\Request\Query\Filter as FilterQuery;
use Magento\Framework\Search\Request\Query\Match as MatchQuery;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * Mapper class. Maps library request to specific adapter dependent query
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
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
     * @var TemporaryStorage
     */
    private $temporaryStorage;

    /**
     * @var string
     * @since 2.2.0
     */
    private $relevanceCalculationMethod;

    /**
     * @var TemporaryStorageFactory
     * @since 2.2.0
     */
    private $temporaryStorageFactory;

    /**
     * @param ScoreBuilderFactory $scoreBuilderFactory
     * @param Builder $filterBuilder
     * @param ConditionManager $conditionManager
     * @param ResourceConnection $resource
     * @param EntityMetadata $entityMetadata
     * @param QueryContainerFactory $queryContainerFactory
     * @param Query\Builder\Match $matchBuilder
     * @param TemporaryStorageFactory $temporaryStorageFactory
     * @param IndexBuilderInterface[] $indexProviders
     * @param string $relevanceCalculationMethod
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ScoreBuilderFactory $scoreBuilderFactory,
        Builder $filterBuilder,
        ConditionManager $conditionManager,
        ResourceConnection $resource,
        EntityMetadata $entityMetadata,
        QueryContainerFactory $queryContainerFactory,
        Match $matchBuilder,
        TemporaryStorageFactory $temporaryStorageFactory,
        array $indexProviders,
        $relevanceCalculationMethod = 'SUM'
    ) {
        $this->scoreBuilderFactory = $scoreBuilderFactory;
        $this->filterBuilder = $filterBuilder;
        $this->conditionManager = $conditionManager;
        $this->resource = $resource;
        $this->entityMetadata = $entityMetadata;
        $this->indexProviders = $indexProviders;
        $this->queryContainerFactory = $queryContainerFactory;
        $this->matchBuilder = $matchBuilder;
        $this->temporaryStorage = $temporaryStorageFactory->create();
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        if (!in_array($relevanceCalculationMethod, ['SUM', 'MAX'], true)) {
            throw new \LogicException('Unsupported relevance calculation method used. Only SUM and MAX are allowed');
        }
        $this->relevanceCalculationMethod = $relevanceCalculationMethod;
    }

    /**
     * Build adapter dependent query
     *
     * @param RequestInterface $request
     * @return Select
     * @throws \LogicException
     * @throws \Zend_Db_Exception
     * @throws \InvalidArgumentException
     */
    public function buildQuery(RequestInterface $request)
    {
        if (!array_key_exists($request->getIndex(), $this->indexProviders)) {
            throw new \LogicException('Index provider not configured');
        }

        $indexBuilder = $this->indexProviders[$request->getIndex()];

        $queryContainer = $this->queryContainerFactory->create(
            [
                'indexBuilder' => $indexBuilder,
                'request' => $request,
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

        $select = $this->addDerivedQueries(
            $request,
            $queryContainer,
            $scoreBuilder,
            $select,
            $indexBuilder
        );

        $select->limit($request->getSize(), $request->getFrom());
        $select->order('relevance ' . Select::SQL_DESC)->order('entity_id ' . Select::SQL_DESC);
        return $select;
    }

    /**
     * Creates Select which wraps search result select
     *
     * It is used to group search results by entity id.
     *
     * @param Select $select
     * @param ScoreBuilder $scoreBuilder
     * @return Select
     */
    private function createAroundSelect(Select $select, ScoreBuilder $scoreBuilder)
    {
        $parentSelect = $this->getConnection()->select();
        $parentSelect->from(
            ['main_select' => $select],
            [
                $this->entityMetadata->getEntityId() => 'entity_id',
                'relevance' => sprintf('%s(%s)', $this->relevanceCalculationMethod, $scoreBuilder->getScoreAlias()),
            ]
        )->group($this->entityMetadata->getEntityId());
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
                $filterCondition = $this->filterBuilder->build($query->getReference(), $conditionType);
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
     * @param QueryContainer $queryContainer
     * @param ScoreBuilder $scoreBuilder
     * @param Select $select
     * @param IndexBuilderInterface $indexBuilder
     * @return Select
     * @throws \Zend_Db_Exception
     */
    private function addDerivedQueries(
        RequestInterface $request,
        QueryContainer $queryContainer,
        ScoreBuilder $scoreBuilder,
        Select $select,
        IndexBuilderInterface $indexBuilder
    ) {
        $matchQueries = $queryContainer->getMatchQueries();
        if (!$matchQueries) {
            $select->columns($scoreBuilder->build());
            $select = $this->createAroundSelect($select, $scoreBuilder);
        } else {
            $matchContainer = array_shift($matchQueries);
            $this->matchBuilder->build(
                $scoreBuilder,
                $select,
                $matchContainer->getRequest(),
                $matchContainer->getConditionType()
            );
            $select->columns($scoreBuilder->build());
            $select = $this->createAroundSelect($select, $scoreBuilder);
            $select = $this->addMatchQueries($request, $select, $indexBuilder, $matchQueries);
        }

        return $select;
    }

    /**
     * @return false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }

    /**
     * @param RequestInterface $request
     * @param Select $select
     * @param IndexBuilderInterface $indexBuilder
     * @param MatchContainer[] $matchQueries
     * @return Select
     */
    private function addMatchQueries(
        RequestInterface $request,
        Select $select,
        IndexBuilderInterface $indexBuilder,
        array $matchQueries
    ) {
        $queriesCount = count($matchQueries);
        if ($queriesCount) {
            $table = $this->temporaryStorage->storeDocumentsFromSelect($select);
            foreach ($matchQueries as $matchContainer) {
                $queriesCount--;
                $matchScoreBuilder = $this->scoreBuilderFactory->create();
                $matchSelect = $this->matchBuilder->build(
                    $matchScoreBuilder,
                    $indexBuilder->build($request),
                    $matchContainer->getRequest(),
                    $matchContainer->getConditionType()
                );
                $select = $this->joinPreviousResultToSelect($matchSelect, $table, $matchScoreBuilder);
                if ($queriesCount) {
                    $previousResultTable = $table;
                    $table = $this->temporaryStorage->storeDocumentsFromSelect($select);
                    $this->getConnection()->dropTable($previousResultTable->getName());
                }
            }
        }
        return $select;
    }

    /**
     * @param Select $query
     * @param Table $previousResultTable
     * @param ScoreBuilder $scoreBuilder
     * @return Select
     * @throws \Zend_Db_Exception
     */
    private function joinPreviousResultToSelect(Select $query, Table $previousResultTable, ScoreBuilder $scoreBuilder)
    {
        $query->joinInner(
            ['previous_results' => $previousResultTable->getName()],
            'previous_results.entity_id = search_index.entity_id',
            []
        );
        $scoreBuilder->addCondition('previous_results.score', false);
        $query->columns($scoreBuilder->build());

        $query = $this->createAroundSelect($query, $scoreBuilder);

        return $query;
    }
}
