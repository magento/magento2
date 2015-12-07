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
     * @var string
     */
    private $fieldName;

    /**
     * @var string
     */
    private $storeId;

    /**
     * @var array
     */
    private $entityIds;

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
        Config $clientConfig,
        $fieldName,
        $storeId,
        $entityIds
    ) {
        $this->connectionManager = $connectionManager;
        $this->fieldMapper = $fieldMapper;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->clientConfig = $clientConfig;
        $this->fieldName = $fieldName;
        $this->storeId = $storeId;
        $this->entityIds = $entityIds;
    }

    /**
     * {@inheritdoc}
     */
    public function load($limit, $offset = null, $lower = null, $upper = null)
    {
        $requestQuery = $from = $to = [];
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        if ($lower) {
            $from = ['gte' => $lower - self::DELTA];
        }
        if ($upper) {
            $to = ['lt' => $upper - self::DELTA];
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
                                            'store_id' => $this->storeId,
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            '_id' => $this->entityIds,
                                        ],
                                    ],
                                    [
                                        'nested' => [
                                            'path' => $this->fieldName,
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
                                                                $this->fieldName.'.price' => array_merge($from, $to),
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
                                $this->fieldName.'.price' => array_merge($from, $to),
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
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        if ($lower) {
            $from = ['gte' => $lower - self::DELTA];
        }
        if ($data) {
            $to = ['lt' => $data - self::DELTA];
        }

        $requestQuery = [
            'index' => $this->clientConfig->getIndexName(),
            'type' => $this->clientConfig->getEntityType(),
            'search_type' => 'count',
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
                                            'store_id' => $this->storeId,
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            '_id' => $this->entityIds,
                                        ],
                                    ],
                                    [
                                        'nested' => [
                                            'path' => $this->fieldName,
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
                                                                $this->fieldName.'.price' => array_merge($from, $to),
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
                                $this->fieldName.'.price' => array_merge($from, $to),
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $queryResult = $this->connectionManager->getConnection()
            ->query($requestQuery);

        $offset = $queryResult['hits']['total'];
        if (!$offset) {
            return false;
        }

        return $this->load($index - $offset + 1, $offset - 1, $lower);
    }

    /**
     * {@inheritdoc}
     */
    public function loadNext($data, $rightIndex, $upper = null)
    {
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();

        $from = ['gt' => $data + self::DELTA];
        $to = ['lt' => $data - self::DELTA];


        $requestCountQuery = [
            'index' => $this->clientConfig->getIndexName(),
            'type' => $this->clientConfig->getEntityType(),
            'search_type' => 'count',
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
                                            'store_id' => $this->storeId,
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            '_id' => $this->entityIds,
                                        ],
                                    ],
                                    [
                                        'nested' => [
                                            'path' => $this->fieldName,
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
                                                                $this->fieldName.'.price' => array_merge($from, $to),
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
                                $this->fieldName.'.price' => array_merge($from, $to),
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $queryCountResult = $this->connectionManager->getConnection()
            ->query($requestCountQuery);

        $offset = $queryCountResult['hits']['total'];
        if (!$offset) {
            return false;
        }

        $from = ['gte' => $data - self::DELTA];
        if ($upper !== null) {
            $to = ['lt' => $data - self::DELTA];
        }

        $requestQuery = [
            'index' => $this->clientConfig->getIndexName(),
            'type' => $this->clientConfig->getEntityType(),
            'search_type' => 'count',
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
                                            'store_id' => $this->storeId,
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            '_id' => $this->entityIds,
                                        ],
                                    ],
                                    [
                                        'nested' => [
                                            'path' => $this->fieldName,
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
                                                                $this->fieldName.'.price' => array_merge($from, $to),
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
                                $this->fieldName.'.price' => array_merge($from, $to),
                            ]
                        ]
                    ]
                ],
                'from' => $offset - 1,
                'size' => $rightIndex - $offset + 1,
            ]
        ];
        $queryResult = $this->connectionManager->getConnection()
            ->query($requestQuery);

        return array_reverse($this->arrayValuesToFloat($queryResult));
    }

    /**
     * @param array $hits
     * 
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
