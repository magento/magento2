<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Dynamic;

class DataProvider implements \Magento\Framework\Search\Dynamic\DataProviderInterface
{
    /**
     * @var \Magento\Elasticsearch\SearchAdapter\ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapperInterface
     */
    protected $fieldMapper;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\Price\Range
     */
    protected $range;

    /**
     * @var \Magento\Framework\Search\Dynamic\IntervalFactory
     */
    protected $intervalFactory;

    /**
     * @var \Magento\Elasticsearch\Model\Config
     */
    protected $clientConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver
     */
    protected $searchIndexNameResolver;

    /**
     * @var string
     */
    protected $indexerId;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $scopeResolver;

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
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver
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
    }

    /**
     * {@inheritdoc}
     */
    public function getRange()
    {
        return $this->range->getPriceRange();
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations(\Magento\Framework\Search\Dynamic\EntityStorage $entityStorage)
    {
        $aggregations = [
            'count' => 0,
            'max' => 0,
            'min' => 0,
            'std' => 0,
        ];
        $entityIds = $entityStorage->getSource();
        $fieldName = $this->fieldMapper->getFieldName('price');
        $storeId = $this->storeManager->getStore()->getId();
        $requestQuery = [
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
                'aggregations' => [
                    'prices' => [
                        'extended_stats' => [
                            'field' => $fieldName,
                        ],
                    ],
                ],
            ],
        ];
        $queryResult = $this->connectionManager->getConnection()
            ->query($requestQuery);

        if (isset($queryResult['aggregations']['prices'])) {
            $aggregations = [
                'count' => $queryResult['aggregations']['prices']['count'],
                'max' => $queryResult['aggregations']['prices']['max'],
                'min' => $queryResult['aggregations']['prices']['min'],
                'std' => $queryResult['aggregations']['prices']['std_deviation'],
            ];
        }

        return $aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterval(
        \Magento\Framework\Search\Request\BucketInterface $bucket,
        array $dimensions,
        \Magento\Framework\Search\Dynamic\EntityStorage $entityStorage
    ) {
        $entityIds = $entityStorage->getSource();
        $fieldName = $this->fieldMapper->getFieldName('price');
        $dimension = current($dimensions);
        $storeId = $this->scopeResolver->getScope($dimension->getValue())->getId();

        return $this->intervalFactory->create([
            'entityIds' => $entityIds,
            'storeId' => $storeId,
            'fieldName' => $fieldName
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregation(
        \Magento\Framework\Search\Request\BucketInterface $bucket,
        array $dimensions,
        $range,
        \Magento\Framework\Search\Dynamic\EntityStorage $entityStorage
    ) {
        $result = [];
        $entityIds = $entityStorage->getSource();
        $fieldName = $this->fieldMapper->getFieldName($bucket->getField());
        $dimension = current($dimensions);
        $storeId = $this->scopeResolver->getScope($dimension->getValue())->getId();
        $requestQuery = [
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
                'aggregations' => [
                    'prices' => [
                        'histogram' => [
                            'field' => $fieldName,
                            'interval' => $range,
                        ],
                    ],
                ],
            ],
        ];
        $queryResult = $this->connectionManager->getConnection()
            ->query($requestQuery);
        foreach ($queryResult['aggregations']['prices']['buckets'] as $bucket) {
            $key = intval($bucket['key'] / $range + 1);
            $result[$key] = $bucket['doc_count'];
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareData($range, array $dbRanges)
    {
        $data = [];
        if (!empty($dbRanges)) {
            $lastIndex = array_keys($dbRanges);
            $lastIndex = $lastIndex[count($lastIndex) - 1];
            foreach ($dbRanges as $index => $count) {
                $fromPrice = $index == 1 ? '' : ($index - 1) * $range;
                $toPrice = $index == $lastIndex ? '' : $index * $range;
                $data[] = [
                    'from' => $fromPrice,
                    'to' => $toPrice,
                    'count' => $count,
                ];
            }
        }
        return $data;
    }
}
