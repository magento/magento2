<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit\Publisher\Config\Validator;

use \Magento\Framework\MessageQueue\Publisher\Config\Validator\ActiveConnection;

class ActiveConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ActiveConnection
     */
    private $model;

    protected function setUp()
    {
        $this->model = new ActiveConnection();
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
            ],
            'pub03' => [
                'topic' => 'pub02',
                'disabled' => false,
            ]
        ];
        $this->model->validate($configData);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage More than 1 enabled connections configured for publisher pub01. More than 1 enabled connections configured for publisher pub02.
     */
    public function testValidateMultipleEnabledConnections()
    {
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
                'connections' => [
                    'con01' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                    'con02' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                ],
            ],
            'pub02' => [
                'topic' => 'pub02',
                'disabled' => false,
                'connections' => [
                    'con01' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                    'con02' => ['name' => 'con1', 'exchange' => 'exchange01', 'disabled' => false],
                ],
            ],
        ];
        $this->model->validate($configData);
    }
}
