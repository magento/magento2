<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter\Aggregation;

use Magento\Framework\Search\Dynamic\IntervalInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\FieldMapperInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Elasticsearch\Model\Config;

class Interval implements IntervalInterface
{
    /**
     * Minimal possible value
     */
    const DELTA = 0.005;

    /**
     * Price field name
     */
    const PRICE = 'price';

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var FieldMapperInterface
     */
    protected $fieldMapper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var Config
     */
    protected $clientConfig;

    /**
     * @param array $query
     * @param ConnectionManager $connectionManager
     * @param FieldMapperInterface $fieldMapper
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     * @param Config $clientConfig
     */
    public function __construct(
        ConnectionManager $connectionManager,
        FieldMapperInterface $fieldMapper,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession,
        Config $clientConfig
    ) {
        $this->connectionManager = $connectionManager;
        $this->fieldMapper = $fieldMapper;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->clientConfig = $clientConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function load($limit, $offset = null, $lower = null, $upper = null)
    {
        $requestQuery = $from = $to = [];
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $storeId = $this->storeManager->getStore()->getId();

        if ($lower) {
            $from = ['gte' => $lower - self::DELTA];
        }
        if ($upper) {
            $to = ['lte' => $upper - self::DELTA];
        }

        $requestQuery = [
            'index' => $this->clientConfig->getIndexName(),
            'type' => $this->clientConfig->getEntityType(),
            'body' => [
                'fields' => [
                    '_id'
                ],
                'query' => [
                    'filtered' => [
                        'query' => [
                            'match_all' => [],
                        ],
                        'filter' => [
                            'bool' => [
                                'must' => [
                                    [
                                        'term' => [
                                            'store_id' => $storeId,
                                        ],
                                    ],
                                    [
                                        'nested' => [
                                            'path' => 'price',
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
                                                        [
                                                            'range' => [
                                                                'price.price' => array_merge($from, $to),
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'sort' => [
                    'price.price' => [
                        'order' => 'asc',
                        'mode' => 'min',
                        'nested_filter' => [
                            'range' => [
                                'price.price' => array_merge($from, $to),
                            ]
                        ]
                    ]
                ],
                'size' => $limit
            ]
        ];

        if ($offset) {
            $filterQuery['from'] = $offset;
        }

        $queryResult = $this->connectionManager->getConnection()
            ->query($requestQuery);

        return $this->arrayValuesToFloat($queryResult['hits']['hits']);
    }

    /**
     * {@inheritdoc}
     */
    public function loadPrevious($data, $index, $lower = null)
    {
        /*$fieldName = $this->fieldMapper->getFieldName('price');
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $storeId = $this->storeManager->getStore()->getId();

        if ($lower) {
            $from = ['gte' => $lower - self::DELTA];
        }
        if ($data) {
            $to = ['lte' => $upper - self::DELTA];
        }

        $requestQuery = [
            'index' => $this->clientConfig->getIndexName(),
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
                                            [
                                                'range' => [
                                                    'price.price' => array_merge($from, $to),
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

        print '<pre>' . print_r($queryResult, true) . '</pre>';
        die;*/

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function loadNext($data, $rightIndex, $upper = null)
    {
        return [];
    }

    /**
     * @param array $documents
     * @return float[]
     */
    private function arrayValuesToFloat($hits)
    {
        $returnPrices = [];
        foreach ($hits as $hit) {
            $returnPrices[] = (float) $hit['sort'][0];
        }

        return $returnPrices;
    }
}
