<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'publishers' => [
        'test-publisher-1' => [
            'name' => 'test-publisher-1',
            'connection' => 'amqp',
            'exchange' => 'magento',
        ],
        'test-publisher-2' => [
            'name' => 'test-publisher-2',
            'connection' => 'db',
            'exchange' => 'magento',
        ],
        'test-publisher-3' => [
            'name' => 'test-publisher-3',
            'connection' => 'amqp',
            'exchange' => 'test-exchange-1',
        ],
    ],
    'topics' => [
        'customer.created' => [
            'name' => 'customer.created',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => \Magento\Customer\Api\Data\CustomerInterface::class
            ],
            'publisher' => 'test-publisher-1',
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            'is_synchronous' => false,
        ],
        'customer.created.one' => [
            'name' => 'customer.created.one',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => \Magento\Customer\Api\Data\CustomerInterface::class
            ],
            'publisher' => 'test-publisher-1',
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            'is_synchronous' => false,
        ],
        'customer.created.one.two' => [
            'name' => 'customer.created.one.two',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => \Magento\Customer\Api\Data\CustomerInterface::class
            ],
            'publisher' => 'test-publisher-1',
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            'is_synchronous' => false,
        ],
        'customer.created.two' => [
            'name' => 'customer.created.two',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => \Magento\Customer\Api\Data\CustomerInterface::class
            ],
            'publisher' => 'test-publisher-1',
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            'is_synchronous' => false,
        ],
        'customer.updated' => [
            'name' => 'customer.updated',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => \Magento\Customer\Api\Data\CustomerInterface::class
            ],
            'publisher' => 'test-publisher-2',
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            'is_synchronous' => false,
        ],
        'customer.deleted' => [
            'name' => 'customer.deleted',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => \Magento\Customer\Api\Data\CustomerInterface::class
            ],
            'publisher' => 'test-publisher-2',
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            'is_synchronous' => false,
        ],
        'cart.created' => [
            'name' => 'cart.created',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => \Magento\Quote\Api\Data\CartInterface::class
            ],
            'publisher' => 'test-publisher-3',
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            'is_synchronous' => false,
        ],
        'cart.created.one' => [
            'name' => 'cart.created.one',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => \Magento\Quote\Api\Data\CartInterface::class
            ],
            'publisher' => 'test-publisher-3',
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            'is_synchronous' => false,
        ],
    ],
    'consumers' => [
        'customerCreatedListener' => [
            'name' => 'customerCreatedListener',
            'queue' => 'test-queue-1',
            'connection' => 'amqp',
            'max_messages' => null,
            'instance_type' => 'Test\Executor',
            'consumer_type' => 'async',
            'handlers' => [
                'customer.created' => [
                    'defaultHandler' => [
                        'type' => 'Data\Type',
                        'method' => 'processMessage'
                    ]
                ],
                'customer.created.one' => [
                    'defaultHandler' => [
                        'type' => 'Data\Type',
                        'method' => 'processMessage'
                    ]
                ],
                'customer.created.one.two' => [
                    'defaultHandler' => [
                        'type' => 'Data\Type',
                        'method' => 'processMessage'
                    ]
                ],
                'customer.created.two' => [
                    'defaultHandler' => [
                        'type' => 'Data\Type',
                        'method' => 'processMessage'
                    ]
                ],
                'customer.updated' => [
                    'defaultHandler' => [
                        'type' => 'Data\Type',
                        'method' => 'processMessage'
                    ]
                ],
                'cart.created' => [
                    'defaultHandler' => [
                        'type' => 'Data\Type',
                        'method' => 'processMessage'
                    ]
                ]
            ]
        ],
        'customerDeletedListener' => [
            'name' => 'customerDeletedListener',
            'queue' => 'test-queue-2',
            'connection' => 'db',
            'max_messages' => '98765',
            'instance_type' => null,
            'consumer_type' => 'async',
            'handlers' => [
                'customer.created' => [
                    'defaultHandler' => [
                        'type' => 'Other\Type',
                        'method' => 'processMessage2'
                    ]
                ],
                'customer.deleted' => [
                    'defaultHandler' => [
                        'type' => 'Other\Type',
                        'method' => 'processMessage2'
                    ]
                ]
            ]
        ],
        'cartCreatedListener' => [
            'name' => 'cartCreatedListener',
            'queue' => 'test-queue-3',
            'connection' => 'amqp',
            'max_messages' => null,
            'instance_type' => null,
            'consumer_type' => 'async',
            'handlers' => [
                'cart.created' => [
                    'defaultHandler' => [
                        'type' => 'Other\Type',
                        'method' => 'processMessage3'
                    ]
                ],
                'cart.created.one' => [
                    'defaultHandler' => [
                        'type' => 'Other\Type',
                        'method' => 'processMessage3'
                    ]
                ]
            ]
        ],
    ],
    'binds' => [
        'customer.created--magento--test-queue-1' =>
            ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.created"],
        'customer.created.one--magento--test-queue-1' =>
            ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.created.one"],
        'customer.created.one.two--magento--test-queue-1' =>
            ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.created.one.two"],
        'customer.created.two--magento--test-queue-1' =>
            ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.created.two"],
        'customer.updated--magento--test-queue-1' =>
            ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.updated"],
        'cart.created--test-exchange-1--test-queue-1' =>
            ['queue' => "test-queue-1", 'exchange' => "test-exchange-1", 'topic' => "cart.created"],
        'customer.created--magento--test-queue-2' =>
            ['queue' => "test-queue-2", 'exchange' => "magento", 'topic' => "customer.created"],
        'customer.deleted--magento--test-queue-2' =>
            ['queue' => "test-queue-2", 'exchange' => "magento", 'topic' => "customer.deleted"],
        'cart.created--magento--test-queue-3' =>
            ['queue' => "test-queue-3", 'exchange' => "magento", 'topic' => "cart.created"],
        'cart.created.one--magento--test-queue-3' =>
            ['queue' => "test-queue-3", 'exchange' => "magento", 'topic' => "cart.created.one"],
        'cart.created--test-exchange-1--test-queue-3' =>
            ['queue' => "test-queue-3", 'exchange' => "test-exchange-1", 'topic' => "cart.created"],
        'customer.*--magento--test-queue-4' =>
            ['queue' => "test-queue-4", 'exchange' => "magento", 'topic' => "customer.*"],
        'customer.#--magento--test-queue-5' =>
            ['queue' => "test-queue-5", 'exchange' => "magento", 'topic' => "customer.#"],
        'customer.*.one--magento--test-queue-6' =>
            ['queue' => "test-queue-6", 'exchange' => "magento", 'topic' => "customer.*.one"],
        '*.created.*--magento--test-queue-7' =>
            ['queue' => "test-queue-7", 'exchange' => "magento", 'topic' => "*.created.*"],
        '*.created.#--magento--test-queue-8' =>
            ['queue' => "test-queue-8", 'exchange' => "magento", 'topic' => "*.created.#"],
        '#--magento--test-queue-9' =>
            ['queue' => "test-queue-9", 'exchange' => "magento", 'topic' => "#"],
    ],
    'exchange_topic_to_queues_map' => [
        'magento--customer.created' => ['test-queue-1', 'test-queue-2', 'test-queue-4', 'test-queue-5', 'test-queue-9'],
        'magento--customer.created.one' =>
            ['test-queue-1', 'test-queue-5', 'test-queue-6', 'test-queue-7', 'test-queue-8', 'test-queue-9'],
        'magento--customer.created.one.two' => ['test-queue-1', 'test-queue-5', 'test-queue-8', 'test-queue-9'],
        'magento--customer.created.two' =>
            ['test-queue-1', 'test-queue-5', 'test-queue-7', 'test-queue-8', 'test-queue-9'],
        'magento--customer.updated' => ['test-queue-1', 'test-queue-4', 'test-queue-5', 'test-queue-9'],
        'test-exchange-1--cart.created' => ['test-queue-1', 'test-queue-3'],
        'magento--customer.deleted' => ['test-queue-2', 'test-queue-4', 'test-queue-5', 'test-queue-9'],
        'magento--cart.created' => ['test-queue-3', 'test-queue-9'],
        'magento--cart.created.one' => ['test-queue-3', 'test-queue-7', 'test-queue-8', 'test-queue-9'],
    ]
];
