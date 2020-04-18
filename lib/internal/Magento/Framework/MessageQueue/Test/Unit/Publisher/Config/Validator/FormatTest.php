<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Publisher\Config\Validator;

use \Magento\Framework\MessageQueue\Publisher\Config\Validator\Format;

class FormatTest extends \PHPUnit\Framework\TestCase
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
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
                'connection' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
            ],
            'pub02' => [
                'topic' => 'pub02',
                'disabled' => false,
                'connection' => ['name' => 'amqp', 'exchange' => 'magento', 'disabled' => true],
            ]
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missing topic field for publisher pub01.
     */
    public function testValidateMissingTopicName()
    {
        $configData = [
            'pub01' => [
                'disabled' => false,
                'connection' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missing disabled field for publisher pub01.
     */
    public function testValidateMissingDisabledField()
    {
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'connection' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missing connection field for publisher pub01.
     */
    public function testValidateMissingConnectionField()
    {
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Invalid connection format for publisher pub01.
     */
    public function testValidateInvalidConnectionFormat()
    {
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
                'connection' => 'con1'
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missing name field for publisher pub01 in connection config.
     */
    public function testValidateInvalidPublisherConnectionName()
    {
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
                'connection' => ['exchange' => 'exchange01', 'disabled' => false],
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missing exchange field for publisher pub01 in connection config.
     */
    public function testValidateInvalidConnectionExchange()
    {
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
                'connection' => ['name' => 'con1', 'disabled' => false],
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missing disabled field for publisher pub01 in connection config.
     */
    public function testValidateInvalidConnectionDisabledField()
    {
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
                'connection' => ['name' => 'con1', 'exchange' => 'default'],
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missing topic field for publisher pub01. Missing disabled field for publisher pub02.
     */
    public function testValidateMultipleExceptions()
    {
        $configData = [
            'pub01' => [
                'disabled' => false,
                'connection' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
            ],
            'pub02' => [
                'topic' => 'pub02',
                'connection' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
            ],
        ];
        $this->model->validate($configData);
    }
}
