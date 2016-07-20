<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit\Publisher\Config\Validator;

use \Magento\Framework\MessageQueue\Publisher\Config\Validator\Format;

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
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
                'connections' => [
                    'con01' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => true],
                    'con02' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                ],
            ],
            'pub02' => [
                'topic' => 'pub02',
                'disabled' => false,
                'connections' => [
                    'con01' => ['name' => 'amqp', 'exchange' => 'magento', 'disabled' => true],
                ]
            ]
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missed topic field for publisher pub01.
     */
    public function testValidateMissedTopicName()
    {
        $configData = [
            'pub01' => [
                'disabled' => false,
                'connections' => [
                    'con01' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                    'con02' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                ],
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missed disabled field for publisher pub01.
     */
    public function testValidateMissedDisabledField()
    {
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'connections' => [
                    'con01' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                    'con02' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                ],
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missed connections field for publisher pub01.
     */
    public function testValidateMissedConnectionsField()
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
     * @expectedExceptionMessage Invalid connections format for publisher pub01.
     */
    public function testValidateInvalidConnectionsFormat()
    {
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
                'connections' => 'con1'
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missed name field for publisher pub01 in connection config.
     */
    public function testValidateInvalidPublisherConnectionName()
    {
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
                'connections' => [
                    'con01' => ['exchange' => 'exchange01', 'disabled' => false],
                ]
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missed exchange field for publisher pub01 in connection config.
     */
    public function testValidateInvalidConnectionExchange()
    {
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
                'connections' => [
                    'con01' => ['name' => 'con1', 'disabled' => false],
                ]
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missed disabled field for publisher pub01 in connection config.
     */
    public function testValidateInvalidConnectionDisabledField()
    {
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
                'connections' => [
                    'con01' => ['name' => 'con1', 'exchange' => 'default'],
                ]
            ],
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Missed topic field for publisher pub01. Missed disabled field for publisher pub02.
     */
    public function testValidateMultipleExceptions()
    {
        $configData = [
            'pub01' => [
                'disabled' => false,
                'connections' => [
                    'con01' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                    'con02' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                ],
            ],
            'pub02' => [
                'topic' => 'pub02',
                'connections' => [
                    'con01' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                    'con02' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                ],
            ],
        ];
        $this->model->validate($configData);
    }
}
