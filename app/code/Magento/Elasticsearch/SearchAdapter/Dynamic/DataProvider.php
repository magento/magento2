<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Dynamic;

use Magento\Elasticsearch\SearchAdapter\QueryAwareInterface;
use Magento\Elasticsearch\SearchAdapter\QueryContainer;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Psr\Log\LoggerInterface;

/**
 * Elastic search data provider
 *
 * @api
 * @since 100.1.0
 */
class DataProvider implements \Magento\Framework\Search\Dynamic\DataProviderInterface, QueryAwareInterface
{
    /**
     * Default field name used to aggregate data
     */
    private const DEFAULT_AGGREGATION_FIELD = 'price';

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\ConnectionManager
     * @since 100.1.0
     */
    protected $connectionManager;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapperInterface
     * @since 100.1.0
     */
    protected $fieldMapper;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Price\Range
     * @since 100.1.0
     */
    protected $range;

    /**
     * @var \Magento\Framework\Search\Dynamic\IntervalFactory
     * @since 100.1.0
     */
    protected $intervalFactory;

    /**
     * @var \Magento\Elasticsearch\Model\Config
     * @deprecated 100.2.0 as this class shouldn't be responsible for query building
     * and should only modify existing query
     * @since 100.1.0
     */
    protected $clientConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @deprecated 100.2.0 as this class shouldn't be responsible for query building
     * and should only modify existing query
     * @since 100.1.0
     */
    protected $storeManager;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver
     * @deprecated 100.2.0 as this class shouldn't be responsible for query building
     * and should only modify existing query
     * @since 100.1.0
     */
    protected $searchIndexNameResolver;

    /**
     * @var string
     * @deprecated 100.2.0 as this class shouldn't be responsible for query building
     * and should only modify existing query
     * @since 100.1.0
     */
    protected $indexerId;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     * @since 100.1.0
     */
    protected $scopeResolver;

    /**
     * @var QueryContainer
     */
    private $queryContainer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $aggregationFieldName;

    /**
     * @param \Magento\Elasticsearch\SearchAdapter\ConnectionManager $connectionManager
     * @param \Magento\Elasticsearch\Model\Adapter\FieldMapperInterface $fieldMapper
     * @param \Magento\Catalog\Model\Layer\Filter\Price\Range $range
     * @param \Magento\Framework\Search\Dynamic\IntervalFactory $intervalFactory
     * @param \Magento\Elasticsearch\Model\Config $clientConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver $searchIndexNameResolver
     * @param string $indexerId
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param QueryContainer|null $queryContainer
     * @param LoggerInterface|null $logger
     * @param string|null $aggregationFieldName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Elasticsearch\SearchAdapter\ConnectionManager $connectionManager,
        \Magento\Elasticsearch\Model\Adapter\FieldMapperInterface $fieldMapper,
        \Magento\Catalog\Model\Layer\Filter\Price\Range $range,
        \Magento\Framework\Search\Dynamic\IntervalFactory $intervalFactory,
        \Magento\Elasticsearch\Model\Config $clientConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver $searchIndexNameResolver,
        $indexerId,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        QueryContainer $queryContainer = null,
        LoggerInterface $logger = null,
        ?string $aggregationFieldName = null
    ) {
        $this->connectionManager = $connectionManager;
        $this->fieldMapper = $fieldMapper;
        $this->range = $range;
        $this->intervalFactory = $intervalFactory;
        $this->clientConfig = $clientConfig;
        $this->storeManager = $storeManager;
        $this->searchIndexNameResolver = $searchIndexNameResolver;
        $this->indexerId = $indexerId;
        $this->scopeResolver = $scopeResolver;
        $this->queryContainer = $queryContainer;
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->aggregationFieldName = $aggregationFieldName ?? self::DEFAULT_AGGREGATION_FIELD;
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    public function getRange()
    {
        return $this->range->getPriceRange();
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    public function getAggregations(EntityStorage $entityStorage)
    {
        $aggregations = [
            'count' => 0,
            'max' => 0,
            'min' => 0,
            'std' => 0,
        ];

        $query = $this->getBasicSearchQuery($entityStorage);

        $fieldName = $this->fieldMapper->getFieldName($this->aggregationFieldName);
        $query['body']['aggregations'] = [
            'prices' => [
                'extended_stats' => [
                    'field' => $fieldName,
                ],
            ],
        ];

        try {
            $queryResult = $this->connectionManager->getConnection()
                ->query($query);
            if (isset($queryResult['aggregations']['prices'])) {
                $aggregations = [
                    'count' => $queryResult['aggregations']['prices']['count'],
                    'max' => $queryResult['aggregations']['prices']['max'],
                    'min' => $queryResult['aggregations']['prices']['min'],
                    'std' => $queryResult['aggregations']['prices']['std_deviation'],
                ];
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $aggregations;
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    public function getInterval(
        \Magento\Framework\Search\Request\BucketInterface $bucket,
        array $dimensions,
        EntityStorage $entityStorage
    ) {
        $entityIds = $entityStorage->getSource();
        $fieldName = $this->fieldMapper->getFieldName($this->aggregationFieldName);
        $dimension = current($dimensions);
        $storeId = $this->scopeResolver->getScope($dimension->getValue())->getId();

        return $this->intervalFactory->create(
            [
                'entityIds' => $entityIds,
                'storeId' => $storeId,
                'fieldName' => $fieldName,
            ]
        );
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    public function getAggregation(
        \Magento\Framework\Search\Request\BucketInterface $bucket,
        array $dimensions,
        $range,
        EntityStorage $entityStorage
    ) {
        $query = $this->getBasicSearchQuery($entityStorage);

        $fieldName = $this->fieldMapper->getFieldName($bucket->getField());
        $query['body']['aggregations'] = [
            'prices' => [
                'histogram' => [
                    'field' => $fieldName,
                    'interval' => (float)$range,
                    'min_doc_count' => 1,
                ],
            ],
        ];

        $result = [];
        try {
            $queryResult = $this->connectionManager->getConnection()
                ->query($query);
            foreach ($queryResult['aggregations']['prices']['buckets'] as $bucket) {
                $key = (int)($bucket['key'] / $range + 1);
                $result[$key] = $bucket['doc_count'];
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $result;
    }

    /**
     * @inheritdoc
     * @since 100.1.0
     */
    public function prepareData($range, array $dbRanges)
    {
        $data = [];
        if (!empty($dbRanges)) {
            foreach ($dbRanges as $index => $count) {
                $fromPrice = $index == 1 ? 0 : ($index - 1) * $range;
                $toPrice = $index * $range;
                $data[] = [
                    'from' => $fromPrice,
                    'to' => $toPrice,
                    'count' => $count,
                ];
            }
        }

        return $data;
    }

    /**
     * Returns a basic search query which can be used for aggregations calculation
     *
     * The query may be requested from a query container if it has been set
     * or may be build by entity storage and dimensions.
     *
     * Building a query by entity storage is actually deprecated as the query
     * built in this way may cause ElasticSearch's TooManyClauses exception.
     *
     * The code which is responsible for building query in-place should be removed someday,
     * but for now it's a question of backward compatibility as this class may be used somewhere else
     * by extension developers and we can't guarantee that they'll pass a query into constructor.
     *
     * @param EntityStorage $entityStorage
     * @param array $dimensions
     * @return array
     */
    private function getBasicSearchQuery(
        EntityStorage $entityStorage,
        array $dimensions = []
    ) {
        if (null !== $this->queryContainer) {
            return $this->queryContainer->getQuery();
        }

        $entityIds = $entityStorage->getSource();

        $dimension = current($dimensions);
        $storeId = false !== $dimension
            ? $this->scopeResolver->getScope($dimension->getValue())->getId()
            : $this->storeManager->getStore()->getId();

        $query = [
            'index' => $this->searchIndexNameResolver->getIndexName($storeId, $this->indexerId),
            'type' => $this->clientConfig->getEntityType(),
            'body' => [
                'fields' => [
                    '_id',
                    '_score',
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'terms' => [
                                    '_id' => $entityIds,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $query;
    }
}
