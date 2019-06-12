<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Topology\Config\Validator;

use Magento\Framework\MessageQueue\Topology\Config\Validator\FieldsTypes;

class FieldsTypesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FieldsTypes
     */
    private $model;

    protected function setUp()
    {
        $this->model = new FieldsTypes();
    }

    public function testValidateValidConfig()
    {
        $configData = [
            'ex01' => [
                'name' => 'ex01',
                'type' => 'topic',
                'connection' => 'amqp',
                'durable' => true,
                'internal' => false,
                'autoDelete' => false,
                'arguments' => ['some' => 'argument'],
                'bindings' => [
                    'bind01' => [
                        'id' => 'bind01',
                        'topic' => 'bind01',
                        'destinationType' => 'queue',
                        'destination' => 'bind01',
                        'disabled' => false,
                        'arguments' => ['some' => 'arguments'],
                    ],
                ],
            ]
        ];
        $this->model->validate($configData);
    }

    /**
     * @dataProvider invalidConfigDataProvider
     * @param array $configData
     * @param string $expectedExceptionMessage
     */
    public function testValidateInvalid($configData, $expectedExceptionMessage)
    {
        $this->expectException('\LogicException');
        $this->expectExceptionMessage($expectedExceptionMessage);
        $this->model->validate($configData);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function invalidConfigDataProvider()
    {
        return [
            'type name' => [
                [
                    'ex01' => [
                        'name' => true,
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'topic' => 'bind01',
                                'destinationType' => 'queue',
                                'destination' => 'bind01',
                                'disabled' => false,
                                'arguments' => ['some' => 'arguments'],
                            ],
                        ],
                    ],
                ],
                "Type of 'name' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'boolean', 'string' was expected."
            ],
            'type type' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 100,
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'topic' => 'bind01',
                                'destinationType' => 'queue',
                                'destination' => 'bind01',
                                'disabled' => false,
                                'arguments' => ['some' => 'arguments'],
                            ],
                        ],
                    ],
                ],
                "Type of 'type' field specified in configuration of 'ex01' exchange is invalid. Given 'integer"
            ],
            'invalid type' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'some',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'topic' => 'bind01',
                                'destinationType' => 'queue',
                                'destination' => 'bind01',
                                'disabled' => false,
                                'arguments' => ['some' => 'arguments'],
                            ],
                        ],
                    ],
                ],
                "Value of 'type' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'some', 'topic' was expected."
            ],
            'type connection' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => false,
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'topic' => 'bind01',
                                'destinationType' => 'queue',
                                'destination' => 'bind01',
                                'disabled' => false,
                                'arguments' => ['some' => 'arguments'],
                            ],
                        ],
                    ],
                ],
                "Type of 'connection' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'boolean', 'string' was expected."
            ],
            'type durable' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => 100,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'topic' => 'bind01',
                                'destinationType' => 'queue',
                                'destination' => 'bind01',
                                'disabled' => false,
                                'arguments' => ['some' => 'arguments'],
                            ],
                        ],
                    ],
                ],
                "Type of 'durable' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'integer', 'boolean' was expected."
            ],
            'type internal' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => null,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'topic' => 'bind01',
                                'destinationType' => 'queue',
                                'destination' => 'bind01',
                                'disabled' => false,
                                'arguments' => ['some' => 'arguments'],
                            ],
                        ],
                    ],
                ],
                "Type of 'internal' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'NULL', 'boolean' was expected."
            ],
            'type autoDelete' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => 1,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'topic' => 'bind01',
                                'destinationType' => 'queue',
                                'destination' => 'bind01',
                                'disabled' => false,
                                'arguments' => ['some' => 'arguments'],
                            ],
                        ],
                    ],
                ],
                "Type of 'autoDelete' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'integer', 'boolean' was expected."
            ],
            'type arguments' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => 'argument',
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'topic' => 'bind01',
                                'destinationType' => 'queue',
                                'destination' => 'bind01',
                                'disabled' => false,
                                'arguments' => ['some' => 'arguments'],
                            ],
                        ],
                    ],
                ],
                "Type of 'arguments' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'string', 'array' was expected."
            ],
            'type bindings' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => false,
                    ],
                ],
                "Type of 'bindings' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'boolean', 'array' was expected."
            ],
            'type binding id' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 100,
                                'destinationType' => 'queue',
                                'destination' => 'queue01',
                                'disabled' => false,
                                'topic' => 'topic01',
                                'arguments' => ['some' => 'arg']
                            ]
                        ],
                    ],
                ],
                "Type of 'id' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'integer', 'string' was expected."
            ],
            'type binding destinationType' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'destinationType' => false,
                                'destination' => 'queue01',
                                'disabled' => false,
                                'topic' => 'topic01',
                                'arguments' => ['some' => 'arg']
                            ]
                        ],
                    ],
                ],
                "Type of 'destinationType' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'boolean', 'string' was expected."
            ],
            'invalid binding destinationType' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'destinationType' => 'test',
                                'destination' => 'queue01',
                                'disabled' => false,
                                'topic' => 'topic01',
                                'arguments' => ['some' => 'arg']
                            ]
                        ],
                    ],
                ],
                "Value of 'destinationType' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'test', 'queue' was expected."
            ],
            'type binding destination' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'destinationType' => 'queue',
                                'destination' => null,
                                'disabled' => false,
                                'topic' => 'topic01',
                                'arguments' => ['some' => 'arg']
                            ]
                        ],
                    ],
                ],
                "Type of 'destination' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'NULL', 'string' was expected."
            ],
            'type binding disabled' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'destinationType' => 'queue',
                                'destination' => 'queue01',
                                'disabled' => 1,
                                'topic' => 'topic01',
                                'arguments' => ['some' => 'arg']
                            ]
                        ],
                    ],
                ],
                "Type of 'disabled' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'integer', 'boolean' was expected."
            ],
            'type binding topic' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'destinationType' => 'queue',
                                'destination' => 'queue01',
                                'disabled' => false,
                                'topic' => false,
                                'arguments' => ['some' => 'arg']
                            ]
                        ]
                    ],
                ],
                "Type of 'topic' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'boolean', 'string' was expected."
            ],
            'type binding arguments' => [
                [
                    'ex01' => [
                        'name' => 'ex01',
                        'type' => 'topic',
                        'connection' => 'amqp',
                        'durable' => true,
                        'internal' => false,
                        'autoDelete' => false,
                        'arguments' => ['some' => 'argument'],
                        'bindings' => [
                            'bind01' => [
                                'id' => 'bind01',
                                'destinationType' => 'queue',
                                'destination' => 'queue01',
                                'disabled' => false,
                                'topic' => 'topic01',
                                'arguments' => 'args'
                            ]
                        ]
                    ],
                ],
                "Type of 'arguments' field specified in configuration of 'ex01' exchange is invalid."
                . " Given 'string', 'array' was expected."
            ],
        ];
    }
}
