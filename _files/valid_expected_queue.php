<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    "publishers" => [
        "demo-publisher-1" => [
            "name" => "demo-publisher-1",
            "connection" => "amqp",
            "exchange" => "magento"
        ],
        "demo-publisher-2" => [
            "name" => "demo-publisher-2",
            "connection" => "db",
            "exchange" => "magento"
        ],
        "test-publisher-1" => [
            "name" => "test-publisher-1",
            "connection" => "amqp",
            "exchange" => "magento"
        ],
        "test-publisher-3" => [
            "name" => "test-publisher-3",
            "connection" => "amqp",
            "exchange" => "test-exchange-1"
        ],
        "amqp-magento" => [
            "name" => "amqp-magento",
            "connection" => "amqp",
            "exchange" => "magento"
        ],
        "test-publisher-5" => [
            "name" => "test-publisher-5",
            "connection" => "amqp",
            "exchange" => "test-exchange-10"
        ]
    ],
    "topics" => [
        "demo.object.created" => [
            "name" => "demo.object.created",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\MysqlMq\\Model\\DataObject"
            ],
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            "publisher" => "demo-publisher-1",
            'is_synchronous' => false,
        ],
        "demo.object.updated" => [
            "name" => "demo.object.updated",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\MysqlMq\\Model\\DataObject"
            ],
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            "publisher" => "demo-publisher-2",
            'is_synchronous' => false,
        ],
        "demo.object.custom.created" => [
            "name" => "demo.object.custom.created",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\MysqlMq\\Model\\DataObject"
            ],
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            "publisher" => "demo-publisher-2",
            'is_synchronous' => false,
        ],
        "test.schema.defined.by.method" => [
            "name" => "test.schema.defined.by.method",
            "schema" => [
                "schema_type" => "method_arguments",
                "schema_value" => [
                    [
                        "param_name" => "dataObject",
                        "param_position" => 0,
                        "is_required" => true,
                        "param_type" => "Magento\\MysqlMq\\Model\\DataObject"
                    ],
                    [
                        "param_name" => "requiredParam",
                        "param_position" => 1,
                        "is_required" => true,
                        "param_type" => "string"
                    ],
                    [
                        "param_name" => "optionalParam",
                        "param_position" => 2,
                        "is_required" => false,
                        "param_type" => "int"
                    ]
                ]
            ],
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            "publisher" => "demo-publisher-2",
            'is_synchronous' => false,
        ],
        "customer.created" => [
            "name" => "customer.created",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
            ],
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            "publisher" => "test-publisher-1",
            'is_synchronous' => false,
        ],
        "customer.created.one" => [
            "name" => "customer.created.one",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
            ],
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            "publisher" => "test-publisher-1",
            'is_synchronous' => false,
        ],
        "customer.created.one.two" => [
            "name" => "customer.created.one.two",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
            ],
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            "publisher" => "test-publisher-1",
            'is_synchronous' => false,
        ],
        "customer.created.two" => [
            "name" => "customer.created.two",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
            ],
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            "publisher" => "test-publisher-1",
            'is_synchronous' => false,
        ],
        "customer.updated" => [
            "name" => "customer.updated",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
            ],
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            "publisher" => "demo-publisher-2",
            'is_synchronous' => false,
        ],
        "customer.deleted" => [
            "name" => "customer.deleted",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
            ],
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            "publisher" => "demo-publisher-2",
            'is_synchronous' => false,
        ],
        "cart.created" => [
            "name" => "cart.created",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Quote\\Api\\Data\\CartInterface"
            ],
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            "publisher" => "test-publisher-3",
            'is_synchronous' => false,
        ],
        "cart.created.one" => [
            "name" => "cart.created.one",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Quote\\Api\\Data\\CartInterface"
            ],
            "response_schema" => [
                "schema_type" => null,
                "schema_value" => null
            ],
            "publisher" => "test-publisher-3",
            'is_synchronous' => false,
        ],
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
                "schema_value" => "Magento\\MysqlMq\\Model\\DataObject"
            ],
            "response_schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInter"
            ],
            "publisher" => "test-publisher-5",
            'is_synchronous' => true,
        ],
    ],
    "consumers" => [
        "demoConsumerQueueOne" => [
            "name" => "demoConsumerQueueOne",
            "queue" => "demo-queue-1",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [
                "demo.object.created" => [
                   "defaultHandler" => [
                        "type" => "\\Magento\\MysqlMq\\Model\\Processor",
                        "method" => "processMessage"
                    ]
                ]
            ],
            "max_messages" => null,
            "instance_type" => null
        ],
        "demoConsumerQueueOneWithException" => [
            "name" => "demoConsumerQueueOneWithException",
            "queue" => "demo-queue-1",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [
                "demo.object.created" => [
                   "defaultHandler" => [
                        "type" => "\\Magento\\MysqlMq\\Model\\Processor",
                        "method" => "processMessageWithException"
                    ]
                ]
            ],
            "max_messages" => null,
            "instance_type" => null
        ],
        "demoConsumerQueueTwo" => [
            "name" => "demoConsumerQueueTwo",
            "queue" => "demo-queue-2",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [
                "demo.object.created" => [
                   "defaultHandler" => [
                        "type" => "\\Magento\\MysqlMq\\Model\\Processor",
                        "method" => "processMessage"
                    ]
                ],
                "demo.object.updated" => [
                   "defaultHandler" => [
                        "type" => "\\Magento\\MysqlMq\\Model\\Processor",
                        "method" => "processMessage"
                    ]
                ]
            ],
            "max_messages" => null,
            "instance_type" => null
        ],
        "demoConsumerQueueThree" => [
            "name" => "demoConsumerQueueThree",
            "queue" => "demo-queue-3",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [

            ],
            "max_messages" => null,
            "instance_type" => null
        ],
        "demoConsumerQueueFour" => [
            "name" => "demoConsumerQueueFour",
            "queue" => "demo-queue-4",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [

            ],
            "max_messages" => null,
            "instance_type" => null
        ],
        "demoConsumerQueueFive" => [
            "name" => "demoConsumerQueueFive",
            "queue" => "demo-queue-5",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [

            ],
            "max_messages" => null,
            "instance_type" => null
        ],
        "delayedOperationConsumer" => [
            "name" => "delayedOperationConsumer",
            "queue" => "demo-queue-6",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [
                "test.schema.defined.by.method" => [
                   "defaultHandler" => [
                        "type" => "Magento\\MysqlMq\\Model\\DataObjectRepository",
                        "method" => "delayedOperation"
                    ]
                ]
            ],
            "max_messages" => null,
            "instance_type" => null
        ],
        "topicBrokerConsumer" => [
            "name" => "topicBrokerConsumer",
            "queue" => "demo-queue-1",
            "connection" => "amqp",
            "consumer_type" => "sync",
            "handlers" => [
                "topic.broker.test" => [
                    "0" => [
                        "type" => "Magento\\MysqlMq\\Model\\Processor",
                        "method" => "processMessage"
                    ]
                ]
            ],
            "max_messages" => null,
            "instance_type" => null
        ]
    ],
    "binds" => [
        "demo.object.created--magento--demo-queue-1" => [
            "queue" => "demo-queue-1",
            "exchange" => "magento",
            "topic" => "demo.object.created"
        ],
        "demo.object.created--magento--demo-queue-2" => [
            "queue" => "demo-queue-2",
            "exchange" => "magento",
            "topic" => "demo.object.created"
        ],
        "demo.object.updated--magento--demo-queue-2" => [
            "queue" => "demo-queue-2",
            "exchange" => "magento",
            "topic" => "demo.object.updated"
        ],
        "test.schema.defined.by.method--magento--demo-queue-6" => [
            "queue" => "demo-queue-6",
            "exchange" => "magento",
            "topic" => "test.schema.defined.by.method"
        ],
        "customer.created--magento--test-queue-1" => [
            "queue" => "test-queue-1",
            "exchange" => "magento",
            "topic" => "customer.created"
        ],
        "customer.created.one--magento--test-queue-1" => [
            "queue" => "test-queue-1",
            "exchange" => "magento",
            "topic" => "customer.created.one"
        ],
        "customer.created.one.two--magento--test-queue-1" => [
            "queue" => "test-queue-1",
            "exchange" => "magento",
            "topic" => "customer.created.one.two"
        ],
        "customer.created.two--magento--test-queue-1" => [
            "queue" => "test-queue-1",
            "exchange" => "magento",
            "topic" => "customer.created.two"
        ],
        "customer.updated--magento--test-queue-1" => [
            "queue" => "test-queue-1",
            "exchange" => "magento",
            "topic" => "customer.updated"
        ],
        "cart.created--test-exchange-1--test-queue-1" => [
            "queue" => "test-queue-1",
            "exchange" => "test-exchange-1",
            "topic" => "cart.created"
        ],
        "customer.created--magento--test-queue-2" => [
            "queue" => "test-queue-2",
            "exchange" => "magento",
            "topic" => "customer.created"
        ],
        "customer.deleted--magento--test-queue-2" => [
            "queue" => "test-queue-2",
            "exchange" => "magento",
            "topic" => "customer.deleted"
        ],
        "cart.created--magento--test-queue-3" => [
            "queue" => "test-queue-3",
            "exchange" => "magento",
            "topic" => "cart.created"
        ],
        "cart.created.one--magento--test-queue-3" => [
            "queue" => "test-queue-3",
            "exchange" => "magento",
            "topic" => "cart.created.one"
        ],
        "cart.created--test-exchange-1--test-queue-3" => [
            "queue" => "test-queue-3",
            "exchange" => "test-exchange-1",
            "topic" => "cart.created"
        ],
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
        "magento--demo.object.created" => [
            "demo-queue-1",
            "demo-queue-2"
        ],
        "magento--demo.object.updated" => [
            "demo-queue-2"
        ],
        "magento--test.schema.defined.by.method" => [
            "demo-queue-6"
        ],
        "magento--customer.created" => [
            "test-queue-1",
            "test-queue-2"
        ],
        "magento--customer.created.one" => [
            "test-queue-1"
        ],
        "magento--customer.created.one.two" => [
            "test-queue-1"
        ],
        "magento--customer.created.two" => [
            "test-queue-1"
        ],
        "magento--customer.updated" => [
            "test-queue-1"
        ],
        "test-exchange-1--cart.created" => [
            "test-queue-1",
            "test-queue-3"
        ],
        "magento--customer.deleted" => [
            "test-queue-2"
        ],
        "magento--cart.created" => [
            "test-queue-3"
        ],
        "magento--cart.created.one" => [
            "test-queue-3"
        ],
        "magento--topic.broker.test" => [
            "demo-queue-1"
        ],
        "test-exchange-10--publisher5.topic" => [
            "demo-queue-1"
        ]
    ]
];
