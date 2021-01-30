<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Topology\Config\QueueConfigItem;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder;
use Magento\Framework\MessageQueue\Topology\Config\Data;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItem\DataMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataMapperTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    private $configDataMock;

    /**
     * @var CommunicationConfig|MockObject
     */
    private $communicationConfigMock;

    /**
     * @var ResponseQueueNameBuilder|MockObject
     */
    private $queueNameBuilderMock;

    /**
     * @var DataMapper
     */
    private $model;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configDataMock = $this->createMock(Data::class);
        $this->communicationConfigMock = $this->createMock(CommunicationConfig::class);
        $this->queueNameBuilderMock = $this->createMock(ResponseQueueNameBuilder::class);
        $this->model = new DataMapper(
            $this->configDataMock,
            $this->communicationConfigMock,
            $this->queueNameBuilderMock
        );
    }

    /**
     * @return void
     *
     * @throws LocalizedException
     */
    public function testGetMappedData(): void
    {
        $data = [
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
                        'topic' => 'topic01',
                        'destinationType' => 'queue',
                        'destination' => 'some.queue',
                        'disabled' => false,
                        'arguments' => ['some' => 'arguments'],
                    ],
                    'bind02' => [
                        'id' => 'bind02',
                        'topic' => 'topic02',
                        'destinationType' => 'queue',
                        'destination' => 'some.queue',
                        'disabled' => false,
                        'arguments' => ['some' => 'arguments'],
                    ],
                ],
            ],
            'ex02' => [
                'name' => 'ex02',
                'type' => 'exchange',
                'connection' => 'amqp',
                'durable' => true,
                'internal' => false,
                'autoDelete' => false,
                'arguments' => ['some' => 'argument'],
                'bindings' => [
                    'bind01' => [
                        'id' => 'bind01',
                        'topic' => 'topic01',
                        'destinationType' => 'exchange',
                        'destination' => 'some.exchange',
                        'disabled' => false,
                        'arguments' => ['some' => 'arguments'],
                    ],
                ],
            ],
        ];

        $communicationMap = [
            ['topic01', ['name' => 'topic01', 'is_synchronous' => true]],
            ['topic02', ['name' => 'topic02', 'is_synchronous' => false]],
        ];

        $this->communicationConfigMock->expects($this->exactly(2))
            ->method('getTopic')
            ->willReturnMap($communicationMap);
        $this->configDataMock->expects($this->once())->method('get')->willReturn($data);
        $this->queueNameBuilderMock->expects($this->once())
            ->method('getQueueName')
            ->with('topic01')
            ->willReturn('responseQueue.topic01');

        $actualResult = $this->model->getMappedData();
        $expectedResult = [
            'responseQueue.topic01--amqp' => [
                'name' => 'responseQueue.topic01',
                'connection' => 'amqp',
                'durable' => true,
                'autoDelete' => false,
                'arguments' => ['some' => 'arguments'],
            ],
            'some.queue--amqp' => [
                'name' => 'some.queue',
                'connection' => 'amqp',
                'durable' => true,
                'autoDelete' => false,
                'arguments' => ['some' => 'arguments'],
            ],
        ];
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return void
     *
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetMappedDataForWildcard(): void
    {
        $data = [
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
                        'topic' => '#',
                        'destinationType' => 'queue',
                        'destination' => 'some.queue',
                        'disabled' => false,
                        'arguments' => ['some' => 'arguments'],
                    ],
                    'bind02' => [
                        'id' => 'bind02',
                        'topic' => '*.*.*',
                        'destinationType' => 'queue',
                        'destination' => 'some.queue',
                        'disabled' => false,
                        'arguments' => ['some' => 'arguments'],
                    ],
                    'bind03' => [
                        'id' => 'bind03',
                        'topic' => 'topic01',
                        'destinationType' => 'queue',
                        'destination' => 'some.queue',
                        'disabled' => false,
                        'arguments' => ['some' => 'arguments'],
                    ],
                ],
            ],
            'ex02' => [
                'name' => 'ex02',
                'type' => 'topic',
                'connection' => 'amqp',
                'durable' => true,
                'internal' => false,
                'autoDelete' => false,
                'arguments' => ['some' => 'argument'],
                'bindings' => [
                    'bind01' => [
                        'id' => 'bind01',
                        'topic' => '#.some.*',
                        'destinationType' => 'queue',
                        'destination' => 'some.queue',
                        'disabled' => false,
                        'arguments' => ['some' => 'arguments'],
                    ],
                ],
            ],
        ];

        $communicationData = [
            'topic01' =>  ['name' => 'topic01', 'is_synchronous' => true],
            'topic02' =>  ['name' => 'topic02', 'is_synchronous' => true],
            'topic03' =>  ['name' => 'topic03', 'is_synchronous' => true],
            'topic04.04.04' =>  ['name' => 'topic04.04.04', 'is_synchronous' => true],
            'topic05.05' =>  ['name' => 'topic05.05', 'is_synchronous' => true],
            'topic06.06.06' =>  ['name' => 'topic06.06.06', 'is_synchronous' => false],
            'topic07' =>  ['name' => 'topic07', 'is_synchronous' => false],
            'topic08.part2.some.test' =>  ['name' => 'topic08.part2.some.test', 'is_synchronous' => true],
        ];

        $this->communicationConfigMock->expects($this->once())
            ->method('getTopic')
            ->with('topic01')
            ->willReturn(['name' => 'topic01', 'is_synchronous' => true]);
        $this->communicationConfigMock->expects($this->any())
            ->method('getTopics')
            ->willReturn($communicationData);
        $this->configDataMock->expects($this->once())->method('get')->willReturn($data);
        $this->queueNameBuilderMock->expects($this->any())
            ->method('getQueueName')
            ->willReturnCallback(function ($value) {
                return 'responseQueue.' . $value;
            });

        $actualResult = $this->model->getMappedData();
        $expectedResult = [
            'responseQueue.topic01--amqp' => [
                'name' => 'responseQueue.topic01',
                'connection' => 'amqp',
                'durable' => true,
                'autoDelete' => false,
                'arguments' => ['some' => 'arguments'],
            ],
            'some.queue--amqp' => [
                'name' => 'some.queue',
                'connection' => 'amqp',
                'durable' => true,
                'autoDelete' => false,
                'arguments' => ['some' => 'arguments'],
            ],
            'responseQueue.topic02--amqp' => [
                'name' => 'responseQueue.topic02',
                'connection' => 'amqp',
                'durable' => true,
                'autoDelete' => false,
                'arguments' => ['some' => 'arguments'],
            ],
            'responseQueue.topic03--amqp' => [
                'name' => 'responseQueue.topic03',
                'connection' => 'amqp',
                'durable' => true,
                'autoDelete' => false,
                'arguments' => ['some' => 'arguments'],
            ],
            'responseQueue.topic04.04.04--amqp' => [
                'name' => 'responseQueue.topic04.04.04',
                'connection' => 'amqp',
                'durable' => true,
                'autoDelete' => false,
                'arguments' => ['some' => 'arguments'],
            ],
            'responseQueue.topic05.05--amqp' => [
                'name' => 'responseQueue.topic05.05',
                'connection' => 'amqp',
                'durable' => true,
                'autoDelete' => false,
                'arguments' => ['some' => 'arguments'],
            ],
            'responseQueue.topic08.part2.some.test--amqp' => [
                'name' => 'responseQueue.topic08.part2.some.test',
                'connection' => 'amqp',
                'durable' => true,
                'autoDelete' => false,
                'arguments' => ['some' => 'arguments'],
            ]
        ];
        $this->assertEquals($expectedResult, $actualResult);
    }
}
