<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    "publishers" => [
        "amqp-magento" => [
            "name" => "amqp-magento",
            "connection" => "amqp",
            "exchange" => "magento"
        ],
        'demo-publisher-1' => [
            'name' => 'demo-publisher-1',
            'connection' => 'amqp',
            "exchange" => "magento"
        ],
        "test-publisher-5" => [
            "name" => "test-publisher-5",
            "connection" => "amqp",
            "exchange" => "test-exchange-10"
        ]
    ],
    "topics" => [
        "topic.broker.test" => [
            "name" => "topic.broker.test",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "string"
            ],
            "response_schema" => [
                "schema_type" => "object",
                "schema_value" => "string"
            ],
            "publisher" => "amqp-magento",
            'is_synchronous' => true,
        ],
        "publisher5.topic" => [
            "name" => "publisher5.topic",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => '\\' . \Magento\MysqlMq\Model\DataObject::class
            ],
            "response_schema" => [
                "schema_type" => "object",
                "schema_value" => \Magento\Customer\Api\Data\CustomerInterface::class
            ],
            "publisher" => "test-publisher-5"
        ]
    ],
    "consumers" => [
        "topicBrokerConsumer" => [
            "name" => "topicBrokerConsumer",
            "queue" => "demo-queue-1",
            "connection" => "amqp",
            "consumer_type" => "sync",
            "handlers" => [
                "topic.broker.test" => [
                    "0" => [
                        "type" => \Magento\MysqlMq\Model\Processor::class,
                        "method" => "processMessage"
                    ]
                ]
            ],
            "max_messages" => null,
            "instance_type" => \Magento\Framework\MessageQueue\ConsumerInterface::class
        ]
    ],
    "binds" => [
        "topic.broker.test--magento--demo-queue-1" => [
            "queue" => "demo-queue-1",
            "exchange" => "magento",
            "topic" => "topic.broker.test"
        ],
        "publisher5.topic--test-exchange-10--demo-queue-1" => [
            "queue" => "demo-queue-1",
            "exchange" => "test-exchange-10",
            "topic" => "publisher5.topic"
        ]
    ],
    "exchange_topic_to_queues_map" => [
        "magento--topic.broker.test" => [
            "demo-queue-1"
        ],
        "test-exchange-10--publisher5.topic" => [
            "demo-queue-1"
        ]
    ]
];
