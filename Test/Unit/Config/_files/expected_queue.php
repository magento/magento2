<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
                'schema_value' => 'Magento\Customer\Api\Data\CustomerInterface'
            ],
            'publisher' => 'test-publisher-1',
        ],
        'customer.created.one' => [
            'name' => 'customer.created.one',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => 'Magento\Customer\Api\Data\CustomerInterface'
            ],
            'publisher' => 'test-publisher-1',
        ],
        'customer.created.one.two' => [
            'name' => 'customer.created.one.two',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => 'Magento\Customer\Api\Data\CustomerInterface'
            ],
            'publisher' => 'test-publisher-1',
        ],
        'customer.created.two' => [
            'name' => 'customer.created.two',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => 'Magento\Customer\Api\Data\CustomerInterface'
            ],
            'publisher' => 'test-publisher-1',
        ],
        'customer.updated' => [
            'name' => 'customer.updated',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => 'Magento\Customer\Api\Data\CustomerInterface'
            ],
            'publisher' => 'test-publisher-2',
        ],
        'customer.deleted' => [
            'name' => 'customer.deleted',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => 'Magento\Customer\Api\Data\CustomerInterface'
            ],
            'publisher' => 'test-publisher-2',
        ],
        'cart.created' => [
            'name' => 'cart.created',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => 'Magento\Quote\Api\Data\CartInterface'
            ],
            'publisher' => 'test-publisher-3',
        ],
        'cart.created.one' => [
            'name' => 'cart.created.one',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => 'Magento\Quote\Api\Data\CartInterface'
            ],
            'publisher' => 'test-publisher-3',
        ],
    ],
    'consumers' => [
        'customerCreatedListener' => [
            'name' => 'customerCreatedListener',
            'queue' => 'test-queue-1',
            'connection' => 'amqp',
            'class' => 'Data\Type',
            'method' => 'processMessage',
            'max_messages' => null,
            'instance_type' => 'Test\Executor',
        ],
        'customerDeletedListener' => [
            'name' => 'customerDeletedListener',
            'queue' => 'test-queue-2',
            'connection' => 'db',
            'class' => 'Other\Type',
            'method' => 'processMessage2',
            'max_messages' => '98765',
            'instance_type' => null,
        ],
        'cartCreatedListener' => [
            'name' => 'cartCreatedListener',
            'queue' => 'test-queue-3',
            'connection' => 'amqp',
            'class' => 'Other\Type',
            'method' => 'processMessage3',
            'max_messages' => null,
            'instance_type' => null,
        ],
    ],
    'binds' => [
        ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.created"],
        ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.created.one"],
        ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.created.one.two"],
        ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.created.two"],
        ['queue' => "test-queue-1", 'exchange' => "magento", 'topic' => "customer.updated"],
        ['queue' => "test-queue-1", 'exchange' => "test-exchange-1", 'topic' => "cart.created"],
        ['queue' => "test-queue-2", 'exchange' => "magento", 'topic' => "customer.created"],
        ['queue' => "test-queue-2", 'exchange' => "magento", 'topic' => "customer.deleted"],
        ['queue' => "test-queue-3", 'exchange' => "magento", 'topic' => "cart.created"],
        ['queue' => "test-queue-3", 'exchange' => "magento", 'topic' => "cart.created.one"],
        ['queue' => "test-queue-3", 'exchange' => "test-exchange-1", 'topic' => "cart.created"],
        ['queue' => "test-queue-4", 'exchange' => "magento", 'topic' => "customer.*"],
        ['queue' => "test-queue-5", 'exchange' => "magento", 'topic' => "customer.#"],
        ['queue' => "test-queue-6", 'exchange' => "magento", 'topic' => "customer.*.one"],
        ['queue' => "test-queue-7", 'exchange' => "magento", 'topic' => "*.created.*"],
        ['queue' => "test-queue-8", 'exchange' => "magento", 'topic' => "*.created.#"],
        ['queue' => "test-queue-9", 'exchange' => "magento", 'topic' => "#"],
    ],
    'exchange_topic_to_queues_map' => [
        'magento--customer.created' => ['test-queue-1', 'test-queue-2', 'test-queue-4', 'test-queue-5', 'test-queue-9'],
        'magento--customer.created.one' => [
            'test-queue-1',
            'test-queue-5',
            'test-queue-6',
            'test-queue-7',
            'test-queue-8',
            'test-queue-9'
        ],
        'magento--customer.created.one.two' => ['test-queue-1', 'test-queue-5', 'test-queue-8', 'test-queue-9'],
        'magento--customer.created.two' => [
            'test-queue-1',
            'test-queue-5',
            'test-queue-7',
            'test-queue-8',
            'test-queue-9'
        ],
        'magento--customer.updated' => ['test-queue-1', 'test-queue-4', 'test-queue-5', 'test-queue-9'],
        'test-exchange-1--cart.created' => ['test-queue-1', 'test-queue-3'],
        'magento--customer.deleted' => ['test-queue-2', 'test-queue-4', 'test-queue-5', 'test-queue-9'],
        'magento--cart.created' => ['test-queue-3', 'test-queue-9'],
        'magento--cart.created.one' => ['test-queue-3', 'test-queue-7', 'test-queue-8', 'test-queue-9'],
    ]
];
