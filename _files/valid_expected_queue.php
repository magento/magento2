<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
                "schema_type" => NULL,
                "schema_value" => NULL
            ],
            "publisher" => "demo-publisher-1"
        ],
        "demo.object.updated" => [
            "name" => "demo.object.updated",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\MysqlMq\\Model\\DataObject"
            ],
            "response_schema" => [
                "schema_type" => NULL,
                "schema_value" => NULL
            ],
            "publisher" => "demo-publisher-2"
        ],
        "demo.object.custom.created" => [
            "name" => "demo.object.custom.created",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\MysqlMq\\Model\\DataObject"
            ],
            "response_schema" => [
                "schema_type" => NULL,
                "schema_value" => NULL
            ],
            "publisher" => "demo-publisher-2"
        ],
        "test.schema.defined.by.method" => [
            "name" => "test.schema.defined.by.method",
            "schema" => [
                "schema_type" => "method_arguments",
                "schema_value" => [
                    [
                        "param_name" => "dataObject",
                        "param_position" => 0,
                        "is_required" => TRUE,
                        "param_type" => "Magento\\MysqlMq\\Model\\DataObject"
                    ],
                    [
                        "param_name" => "requiredParam",
                        "param_position" => 1,
                        "is_required" => TRUE,
                        "param_type" => "string"
                    ],
                    [
                        "param_name" => "optionalParam",
                        "param_position" => 2,
                        "is_required" => FALSE,
                        "param_type" => "int"
                    ]
                ]
            ],
            "response_schema" => [
                "schema_type" => NULL,
                "schema_value" => NULL
            ],
            "publisher" => "demo-publisher-2"
        ],
        "customer.created" => [
            "name" => "customer.created",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
            ],
            "response_schema" => [
                "schema_type" => NULL,
                "schema_value" => NULL
            ],
            "publisher" => "test-publisher-1"
        ],
        "customer.created.one" => [
            "name" => "customer.created.one",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
            ],
            "response_schema" => [
                "schema_type" => NULL,
                "schema_value" => NULL
            ],
            "publisher" => "test-publisher-1"
        ],
        "customer.created.one.two" => [
            "name" => "customer.created.one.two",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
            ],
            "response_schema" => [
                "schema_type" => NULL,
                "schema_value" => NULL
            ],
            "publisher" => "test-publisher-1"
        ],
        "customer.created.two" => [
            "name" => "customer.created.two",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
            ],
            "response_schema" => [
                "schema_type" => NULL,
                "schema_value" => NULL
            ],
            "publisher" => "test-publisher-1"
        ],
        "customer.updated" => [
            "name" => "customer.updated",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
            ],
            "response_schema" => [
                "schema_type" => NULL,
                "schema_value" => NULL
            ],
            "publisher" => "demo-publisher-2"
        ],
        "customer.deleted" => [
            "name" => "customer.deleted",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Customer\\Api\\Data\\CustomerInterface"
            ],
            "response_schema" => [
                "schema_type" => NULL,
                "schema_value" => NULL
            ],
            "publisher" => "demo-publisher-2"
        ],
        "cart.created" => [
            "name" => "cart.created",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Quote\\Api\\Data\\CartInterface"
            ],
            "response_schema" => [
                "schema_type" => NULL,
                "schema_value" => NULL
            ],
            "publisher" => "test-publisher-3"
        ],
        "cart.created.one" => [
            "name" => "cart.created.one",
            "schema" => [
                "schema_type" => "object",
                "schema_value" => "Magento\\Quote\\Api\\Data\\CartInterface"
            ],
            "response_schema" => [
                "schema_type" => NULL,
                "schema_value" => NULL
            ],
            "publisher" => "test-publisher-3"
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
            "publisher" => "amqp-magento"
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
            "publisher" => "test-publisher-5"
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
                    [
                        "type" => "\\Magento\\MysqlMq\\Model\\Processor",
                        "method" => "processMessage"
                    ]
                ]
            ],
            "max_messages" => NULL,
            "instance_type" => NULL
        ],
        "demoConsumerQueueOneWithException" => [
            "name" => "demoConsumerQueueOneWithException",
            "queue" => "demo-queue-1",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [
                "demo.object.created" => [
                    [
                        "type" => "\\Magento\\MysqlMq\\Model\\Processor",
                        "method" => "processMessageWithException"
                    ]
                ]
            ],
            "max_messages" => NULL,
            "instance_type" => NULL
        ],
        "demoConsumerQueueTwo" => [
            "name" => "demoConsumerQueueTwo",
            "queue" => "demo-queue-2",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [
                "demo.object.created" => [
                    [
                        "type" => "\\Magento\\MysqlMq\\Model\\Processor",
                        "method" => "processMessage"
                    ]
                ],
                "demo.object.updated" => [
                    [
                        "type" => "\\Magento\\MysqlMq\\Model\\Processor",
                        "method" => "processMessage"
                    ]
                ]
            ],
            "max_messages" => NULL,
            "instance_type" => NULL
        ],
        "demoConsumerQueueThree" => [
            "name" => "demoConsumerQueueThree",
            "queue" => "demo-queue-3",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [

            ],
            "max_messages" => NULL,
            "instance_type" => NULL
        ],
        "demoConsumerQueueFour" => [
            "name" => "demoConsumerQueueFour",
            "queue" => "demo-queue-4",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [

            ],
            "max_messages" => NULL,
            "instance_type" => NULL
        ],
        "demoConsumerQueueFive" => [
            "name" => "demoConsumerQueueFive",
            "queue" => "demo-queue-5",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [

            ],
            "max_messages" => NULL,
            "instance_type" => NULL
        ],
        "delayedOperationConsumer" => [
            "name" => "delayedOperationConsumer",
            "queue" => "demo-queue-6",
            "connection" => "db",
            "consumer_type" => "async",
            "handlers" => [
                "test.schema.defined.by.method" => [
                    [
                        "type" => "Magento\\MysqlMq\\Model\\DataObjectRepository",
                        "method" => "delayedOperation"
                    ]
                ]
            ],
            "max_messages" => NULL,
            "instance_type" => NULL
        ],
        "topicBrokerConsumer" => [
            "name" => "topicBrokerConsumer",
            "queue" => "demo-queue-1",
            "connection" => "amqp",
            "consumer_type" => "sync",
            "handlers" => [
                "topic.broker.test" => [
                    "topicBrokerHandler" => [
                        "type" => "Magento\\MysqlMq\\Model\\Processor",
                        "method" => "processMessage"
                    ]
                ]
            ],
            "max_messages" => NULL,
            "instance_type" => NULL
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
