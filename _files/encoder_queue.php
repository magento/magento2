<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'publishers' => [
        'test-queue' => [
            'name' => 'test-queue',
            'connection' => 'amqp',
            'exchange' => 'magento',
        ],
        'test-queue-2' => [
            'name' => 'test-queue-2',
            'connection' => 'db',
            'exchange' => 'magento',
        ],
    ],
    'topics' => [
        'customer.created' => [
            'name' => 'customer.created',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => 'Magento\Customer\Api\Data\CustomerInterface',
            ],
            'response_schema' => [
                'schema_type' => null,
                'schema_value' => null,
            ],
            'is_synchronous' => false,
            'publisher' => 'test-queue',
        ],
        'customer.list.retrieved' => [
            'name' => 'customer.list.retrieved',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => 'Magento\Customer\Api\Data\CustomerInterface[]',
            ],
            'response_schema' => [
                'schema_type' => null,
                'schema_value' => null,
            ],
            'is_synchronous' => false,
            'publisher' => 'test-queue-2',
        ],
        'customer.updated' => [
            'name' => 'customer.updated',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => 'Magento\Customer\Api\Data\CustomerInterface',
            ],
            'response_schema' => [
                'schema_type' => null,
                'schema_value' => null,
            ],
            'is_synchronous' => false,
            'publisher' => 'test-queue-2',
        ],
        'customer.deleted' => [
            'name' => 'customer.deleted',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => 'Magento\Customer\Api\Data\CustomerInterface',
            ],
            'response_schema' => [
                'schema_type' => null,
                'schema_value' => null,
            ],
            'is_synchronous' => false,
            'publisher' => 'test-queue-2',
        ],
        'product.created' => [
            'name' => 'product.created',
            'schema' => [
                'schema_type' => 'object',
                'schema_value' => 'Magento\Catalog\Api\Data\ProductInterface',
            ],
            'response_schema' => [
                'schema_type' => null,
                'schema_value' => null,
            ],
            'is_synchronous' => false,
            'publisher' => 'test-queue',
        ],
    ],
    'consumers' => [],
    'binds' => [],
    'exchange_topic_to_queues_map' => [],
];
