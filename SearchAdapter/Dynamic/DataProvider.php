<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Dynamic;

use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Framework\Search\Dynamic\DataProviderInterface;
use Magento\Framework\Search\Dynamic\EntityStorage;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Dynamic\IntervalFactory;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Elasticsearch\SearchAdapter\SearchIndexNameResolver;

class DataProvider implements DataProviderInterface
{
    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var FieldMapperInterface
     */
    protected $fieldMapper;

    /**
     * @var Range
     */
    protected $range;

    /**
     * @var IntervalFactory
     */
    protected $intervalFactory;

    /**
     * @var Config
     */
    protected $clientConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var SearchIndexNameResolver
     */
    protected $searchIndexNameResolver;

    /**
     * @var string
     */
    protected $indexerId;

    /**
     * @param ConnectionManager $connectionManager
     * @param FieldMapperInterface $fieldMapper
     * @param Range $range
     * @param IntervalFactory $intervalFactory
     * @param Config $clientConfig
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param SearchIndexNameResolver $searchIndexNameResolver
     * @param string $indexerId
     */
    public function __construct(
        ConnectionManager $connectionManager,
        FieldMapperInterface $fieldMapper,
        Range $range,
        IntervalFactory $intervalFactory,
        Config $clientConfig,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        SearchIndexNameResolver $searchIndexNameResolver,
        $indexerId
    ) {
        $this->connectionManager = $connectionManager;
        $this->fieldMapper = $fieldMapper;
        $this->range = $range;
        $this->intervalFactory = $intervalFactory;
        $this->clientConfig = $clientConfig;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->searchIndexNameResolver = $searchIndexNameResolver;
        $this->indexerId = $indexerId;
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
    public function getAggregations(EntityStorage $entityStorage)
    {
        $aggregations = [
            'count' => 0,
            'max' => 0,
            'min' => 0,
            'std' => 0,
        ];
        $entityIds = $entityStorage->getSource();
        $fieldName = $this->fieldMapper->getFieldName('price');
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
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
                                'term' => [
                                    'store_id' => $storeId,
                                ],
                            ],
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
                        'nested' => [
                            'path' => $fieldName,
                        ],
                        'aggregations' => [
                            'price_filter' => [
                                'filter' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'term' => [
                                                    'price.customer_group_id' => $customerGroupId,
                                                ],
                                            ],
                                            [
                                                'term' => [
                                                    'price.website_id' => $websiteId,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'aggregations' => [
                                    'price_stats' => [
                                        'extended_stats' => [
                                            'field' => $fieldName . '.price',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $queryResult = $this->connectionManager->getConnection()
            ->query($requestQuery);

        if (isset($queryResult['aggregations']['prices']['price_filter']['price_stats'])) {
            $aggregations = [
                'count' => $queryResult['aggregations']['prices']['price_filter']['price_stats']['count'],
                'max' => $queryResult['aggregations']['prices']['price_filter']['price_stats']['max'],
                'min' => $queryResult['aggregations']['prices']['price_filter']['price_stats']['min'],
                'std' => $queryResult['aggregations']['prices']['price_filter']['price_stats']['std_deviation'],
            ];
        }

        return $aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterval(
        BucketInterface $bucket,
        array $dimensions,
        EntityStorage $entityStorage
    ) {
        $entityIds = $entityStorage->getSource();
        $fieldName = $this->fieldMapper->getFieldName('price');
        $dimension = current($dimensions);
        $storeId = $dimension->getValue();

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
        BucketInterface $bucket,
        array $dimensions,
        $range,
        EntityStorage $entityStorage
    ) {
        $result = [];
        $entityIds = $entityStorage->getSource();
        $fieldName = $this->fieldMapper->getFieldName($bucket->getField());
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $dimension = current($dimensions);
        $storeId = $dimension->getValue();
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
                                'term' => [
                                    'store_id' => $storeId,
                                ],
                            ],
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
                        'nested' => [
                            'path' => $fieldName,
                        ],
                        'aggregations' => [
                            'price_filter' => [
                                'filter' => [
                                    'bool' => [
                                        'must' => [
                                            [
                                                'term' => [
                                                    'price.customer_group_id' => $customerGroupId,
                                                ],
                                            ],
                                            [
                                                'term' => [
                                                    'price.website_id' => $websiteId,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'aggregations' => [
                                    'price_stats' => [
                                        'histogram' => [
                                            'field' => $fieldName . '.price',
                                            'interval' => $range,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $queryResult = $this->connectionManager->getConnection()
            ->query($requestQuery);
        foreach ($queryResult['aggregations']['prices']['price_filter']['price_stats']['buckets'] as $bucket) {
            $result[$bucket['key'] / $range + 1] = $bucket['doc_count'];
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
