<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'queue' => [
        'publishers' => [
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
            "publisher5.topic" => [
                "name" => "publisher5.topic",
                "schema" => [
                    "schema_type" => "object",
                    "schema_value" => "Magento\\MysqlMq\\Model\\DataObject"
                ],
                "response_schema" => [
                    "schema_type" => "object",
                    "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
                ],
                "publisher" => "test-publisher-5"
            ]
        ],
        "binds" => [
            "publisher5.topic--test-exchange-10--demo-queue-1" => [
                "queue" => "demo-queue-1",
                "exchange" => "test-exchange-10",
                "topic" => "publisher5.topic"
            ]
        ],
        "exchange_topic_to_queues_map" => [
            "test-exchange-10--publisher5.topic" => [
                "demo-queue-1"
            ]
        ]
    ]
];
