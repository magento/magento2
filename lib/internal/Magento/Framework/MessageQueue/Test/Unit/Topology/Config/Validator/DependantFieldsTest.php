<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Topology\Config\Validator;

use \Magento\Framework\MessageQueue\Topology\Config\Validator\DependentFields;

class DependantFieldsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DependentFields
     */
    private $model;

    protected function setUp()
    {
        $this->model = new DependentFields();
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
            ],
            'ex02' => [
                'name' => 'ex01',
                'type' => 'headers',
                'connection' => 'amqp',
                'durable' => true,
                'internal' => false,
                'autoDelete' => false,
                'arguments' => ['some' => 'argument'],
                'bindings' => [
                    'bind01' => [
                        'id' => 'bind01',
                        'destinationType' => 'queue',
                        'destination' => 'some.queue',
                        'disabled' => false,
                        'arguments' => ['some' => 'arguments'],
                    ],
                ],
            ],
        ];
        $this->model->validate($configData);
    }

    public function testValidateMissingTopicField()
    {
        $expectedMessage = "Topic name is required for topic based exchange: ex01";
        $this->expectException('\LogicException');
        $this->expectExceptionMessage($expectedMessage);
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
}
