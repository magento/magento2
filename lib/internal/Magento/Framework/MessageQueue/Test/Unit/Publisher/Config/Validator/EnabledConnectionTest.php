<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit\Publisher\Config\Validator;

use \Magento\Framework\MessageQueue\Publisher\Config\Validator\EnabledConnection;

class EnabledConnectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EnabledConnection
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new EnabledConnection();
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

    public function testValidateMultipleEnabledConnections()
    {
        $this->expectException('\LogicException');
        $this->expectExceptionMessage(
            'More than 1 enabled connections configured for publisher pub01. ' .
            'More than 1 enabled connections configured for publisher pub02.'
        );
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
