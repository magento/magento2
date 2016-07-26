<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Topology\Config\QueueConfigItem;

use Magento\Framework\MessageQueue\Topology\Config\Data;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItem\DataMapper;

/**
 * @codingStandardsIgnoreFile
 */
class DataMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $communicationConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $queueNameBuilder;

    /**
     * @var DataMapper
     */
    private $model;


    protected function setUp()
    {
        $this->configData = $this->getMock(Data::class, [], [], '', false, false);
        $this->communicationConfig = $this->getMock(CommunicationConfig::class);
        $this->queueNameBuilder = $this->getMock(ResponseQueueNameBuilder::class, [], [], '', false, false);
        $this->model = new DataMapper($this->configData, $this->communicationConfig, $this->queueNameBuilder);
    }

    public function testGetMappedData()
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
                'name' => 'ex01',
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

        $this->communicationConfig->expects($this->exactly(2))->method('getTopic')->willReturnMap($communicationMap);
        $this->configData->expects($this->once())->method('get')->willReturn($data);
        $this->queueNameBuilder->expects($this->once())
            ->method('getQueueName')
            ->with('topic01')
            ->willReturn('responseQueue.topic01');

        $actualResult = $this->model->getMappedData();
        $expectedResult = [
            'responseQueue.topic01-amqp' => [
                'name' => 'responseQueue.topic01',
                'connection' => 'amqp',
                'durable' => true,
                'autoDelete' => false,
                'arguments' => [],
            ],
            'some.queue-amqp' => [
                'name' => 'some.queue',
                'connection' => 'amqp',
                'durable' => true,
                'autoDelete' => false,
                'arguments' => [],
            ],
        ];
        $this->assertEquals($expectedResult, $actualResult);
    }
}
