<?php declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'publishers' => [
        'amqp-ex.01' => [
            'name' => 'amqp-ex.01',
            'connection' => 'amqp',
            'exchange' => 'ex.01',
        ],
        'db-magento' => [
            'name' => 'db-magento',
            'connection' => 'db',
            'exchange' => 'magento',
        ],
        '-magento' => [
            'name' => '-magento',
            'connection' => null,
            'exchange' => 'magento',
        ],
    ],
    'binds' => [
        'top.01--ex.01--q.01'   => ['queue' => 'q.01', 'exchange' => 'ex.01',   'topic' => 'top.01'],
        'top.03--magento--q.03' => ['queue' => 'q.03', 'exchange' => 'magento', 'topic' => 'top.03'],
        'top.04--magento--q.04' => ['queue' => 'q.04', 'exchange' => 'magento', 'topic' => 'top.04'],
        'top.04--magento--q.05' => ['queue' => 'q.05', 'exchange' => 'magento', 'topic' => 'top.04'],
        'top.04--magento--q.06' => ['queue' => 'q.06', 'exchange' => 'magento', 'topic' => 'top.04'],
        'top.03--magento--q.04' => ['queue' => 'q.04', 'exchange' => 'magento', 'topic' => 'top.03'],
        'user.created.remote--magento--q.log' => [
            'queue' => 'q.log', 'exchange' => 'magento', 'topic' => 'user.created.remote'
        ],
        'product.created.local--magento--q.log' => [
            'queue' => 'q.log', 'exchange' => 'magento', 'topic' => 'product.created.local'
        ],
    ],
    'exchange_topic_to_queues_map' => [
        'amqp-ex.01--top.01' => ['q.01'],
        'db-magento--top.04' => ['q.04', 'q.05', 'q.06'],
        '-magento--user.created.remote' => ['q.log'],
        '-magento--product.created.local' => ['q.log'],
        '-magento--top.03' => ['q.03','q.04']
    ],
    'consumers' => [
        'cons.01' => [
            'name' => 'cons.01',
            'handlers' => [
                'top.01' => [
                    '0' => [
                        'type' => 'Magento\Handler\Class\Name',
                        'method' => 'methodName'
                    ]
                ]
            ],
            'instance_type' => 'Magento\Consumer\Instance',
            'consumer_type' => 'async',
            'max_messages' => '512',
            'connection' => 'amqp',
            'queue' => 'q.01'
        ],
        'cons.03' => [
            'name' => 'cons.03',
            'handlers' => [
                'top.03' => [
                    '0' => [
                        'type' => CustomerRepositoryInterface::class,
                        'method' => 'save',
                    ],
                    '1' => [
                        'type' => CustomerRepositoryInterface::class,
                        'method' => 'delete',
                    ],
                ]
            ],
            'instance_type' => 'Magento\Framework\MessageQueue\ConsumerInterface',
            'consumer_type' => 'async',
            'max_messages' => null,
            'connection' => null,
            'queue' => 'q.03'
        ],
        'cons.04' => [
            'name' => 'cons.04',
            'handlers' => [
                'top.04' => [
                    '0' => [
                        'type' => 'Magento\Handler\Class\Name',
                        'method' => 'methodName'
                    ]
                ]
            ],
            'instance_type' => 'Magento\Consumer\Instance',
            'consumer_type' => 'async',
            'max_messages' => '512',
            'connection' => 'db',
            'queue' => 'q.04'
        ],
        'cons.05' => [
            'name' => 'cons.05',
            'handlers' => [
                'top.04' => [
                    '0' => [
                        'type' => 'Magento\Handler\Class\Name',
                        'method' => 'methodName'
                    ]
                ]
            ],
            'instance_type' => 'Magento\Consumer\Instance',
            'consumer_type' => 'async',
            'max_messages' => '512',
            'connection' => 'db',
            'queue' => 'q.05'
        ],
        'cons.06' => [
            'name' => 'cons.06',
            'handlers' => [
                'top.04' => [
                    '0' => [
                        'type' => 'Magento\Handler\Class\Name',
                        'method' => 'methodName'
                    ]
                ]
            ],
            'instance_type' => 'Magento\Consumer\Instance',
            'consumer_type' => 'async',
            'max_messages' => '512',
            'connection' => 'db',
            'queue' => 'q.06'
        ],
        'cons.07' => [
            'name' => 'cons.07',
            'handlers' => [
                'top.03' => [
                    '0' => [
                        'type' => CustomerRepositoryInterface::class,
                        'method' => 'save',
                    ],
                    '1' => [
                        'type' => CustomerRepositoryInterface::class,
                        'method' => 'delete',
                    ],
                ]
            ],
            'instance_type' => 'Magento\Framework\MessageQueue\ConsumerInterface',
            'consumer_type' => 'async',
            'max_messages' => null,
            'connection' => null,
            'queue' => 'q.04'
        ],
        'cons.logger' => [
            'name' => 'cons.logger',
            'handlers' => [
                'product.created.local' => [
                    '0' => [
                        'type' => 'Magento\Handler\Class\Name',
                        'method' => 'logger'
                    ]
                ]
            ],
            'instance_type' => 'Magento\Framework\MessageQueue\ConsumerInterface',
            'consumer_type' => 'async',
            'max_messages' => null,
            'connection' => null,
            'queue' => 'q.log'
        ],
    ],
    'topics' => [
        'top.01' => [
            'name' => 'top.01',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => CustomerInterface::class
            ],
            'response_schema' => [
                'schema_type' => 'object',
                'schema_value' => CustomerInterface::class
            ],
            'publisher' => 'amqp-ex.01',
            'is_synchronous' => false,
        ],
        'top.03' => [
            'name' => 'top.03',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => CustomerInterface::class
            ],
            'response_schema' => [
                'schema_type' => 'object',
                'schema_value' => CustomerInterface::class
            ],
            'publisher' => '-magento',
            'is_synchronous' => false,
        ],
        'top.04' => [
            'name' => 'top.04',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => CustomerInterface::class
            ],
            'response_schema' => [
                'schema_type' => 'object',
                'schema_value' => CustomerInterface::class
            ],
            'publisher' => 'db-magento',
            'is_synchronous' => false,
        ],
        'user.created.remote' => [
            'name' => 'user.created.remote',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => CustomerInterface::class
            ],
            'response_schema' => [
                'schema_type' => 'object',
                'schema_value' => CustomerInterface::class
            ],
            'publisher' => '-magento',
            'is_synchronous' => false,
        ],
        'product.created.local' => [
            'name' => 'product.created.local',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => CustomerInterface::class
            ],
            'response_schema' => [
                'schema_type' => 'object',
                'schema_value' => CustomerInterface::class
            ],
            'publisher' => '-magento',
            'is_synchronous' => false,
        ],
    ]

];
