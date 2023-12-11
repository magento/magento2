<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Publisher\Config\Validator;

use Magento\Framework\MessageQueue\Publisher\Config\Validator\Format;
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    /**
     * @var Format
     */
    private $model;

    protected function setUp(): void
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

    public function testValidateMissingTopicName()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Missing topic field for publisher pub01.');
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

    public function testValidateMissingDisabledField()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Missing disabled field for publisher pub01.');
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

    public function testValidateMissingConnectionsField()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Missing connections field for publisher pub01.');
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
            ],
        ];
        $this->model->validate($configData);
    }

    public function testValidateInvalidConnectionsFormat()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Invalid connections format for publisher pub01.');
        $configData = [
            'pub01' => [
                'topic' => 'pub01',
                'disabled' => false,
                'connections' => 'con1'
            ],
        ];
        $this->model->validate($configData);
    }

    public function testValidateInvalidPublisherConnectionName()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Missing name field for publisher pub01 in connection config.');
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

    public function testValidateInvalidConnectionExchange()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Missing exchange field for publisher pub01 in connection config.');
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

    public function testValidateInvalidConnectionDisabledField()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage('Missing disabled field for publisher pub01 in connection config.');
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

    public function testValidateMultipleExceptions()
    {
        $this->expectException('LogicException');
        $this->expectExceptionMessage(
            'Missing topic field for publisher pub01. Missing disabled field for publisher pub02.'
        );
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
