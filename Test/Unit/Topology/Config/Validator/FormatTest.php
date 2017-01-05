<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Topology\Config\Validator;

use \Magento\Framework\MessageQueue\Topology\Config\Validator\Format;

class FormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Format
     */
    private $model;

    protected function setUp()
    {
        $this->model = new Format();
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

    public function testValidateMissingRequiredExchangeFields()
    {
        $expectedMessage = "Missing [name] field for exchange ex01." . PHP_EOL .
            "Missing [type] field for exchange ex01." . PHP_EOL .
            "Missing [connection] field for exchange ex01." . PHP_EOL .
            "Missing [durable] field for exchange ex01." . PHP_EOL .
            "Missing [autoDelete] field for exchange ex01." . PHP_EOL .
            "Missing [internal] field for exchange ex01." . PHP_EOL .
            "Missing [arguments] field for exchange ex01.";
        $this->setExpectedException('\LogicException', $expectedMessage);
        $configData = [
            'ex01' => [
                'invalid' => 'format',
                'bindings' => [
                    'bind01' => [
                        'id' => 'bind01',
                        'topic' => 'bind01',
                        'destinationType' => 'bind01',
                        'destination' => 'bind01',
                        'disabled' => false,
                        'arguments' => ['some' => 'arguments'],
                    ],
                ],
            ]
        ];
        $this->model->validate($configData);
    }

    public function testValidateMissingRequiredBindingFields()
    {
        $expectedMessage = "Missing [id] field for binding ex01 in exchange config." . PHP_EOL .
            "Missing [destinationType] field for binding ex01 in exchange config." . PHP_EOL .
            "Missing [destination] field for binding ex01 in exchange config." . PHP_EOL .
            "Missing [disabled] field for binding ex01 in exchange config." . PHP_EOL .
            "Missing [topic] field for binding ex01 in exchange config." . PHP_EOL .
            "Missing [arguments] field for binding ex01 in exchange config.";
        $this->setExpectedException('\LogicException', $expectedMessage);
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
                        'invalid' => 'format'
                    ],
                ],
            ]
        ];
        $this->model->validate($configData);
    }

    public function testValidateInvalidBindingsFormat()
    {
        $expectedMessage = "Invalid bindings format for exchange ex01.";
        $this->setExpectedException('\LogicException', $expectedMessage);
        $configData = [
            'ex01' => [
                'name' => 'ex01',
                'type' => 'topic',
                'connection' => 'amqp',
                'durable' => true,
                'internal' => false,
                'autoDelete' => false,
                'arguments' => ['some' => 'argument'],
                'bindings' => 'binding'
            ]
        ];
        $this->model->validate($configData);
    }
}
