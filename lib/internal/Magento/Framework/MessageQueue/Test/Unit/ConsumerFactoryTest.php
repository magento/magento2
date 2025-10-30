<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\DataObject;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConsumerFactoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /** @var CommunicationConfig|MockObject */
    protected $communicationConfigMock;

    /** @var ConsumerConfig|MockObject */
    protected $consumerConfigMock;

    const TEST_CONSUMER_NAME = "test_consumer_name";
    const TEST_CONSUMER_QUEUE = "test_consumer_queue";
    const TEST_CONSUMER_METHOD = "test_consumer_method";

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->communicationConfigMock = $this->getMockBuilder(CommunicationConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->consumerConfigMock = $this->getMockBuilder(ConsumerConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testUndeclaredConsumerName()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('pecified consumer "test_consumer_name" is not declared.');
        $consumerFactory = $this->objectManager->getObject(ConsumerFactory::class);
        $this->objectManager->setBackwardCompatibleProperty(
            $consumerFactory,
            'communicationConfig',
            $this->communicationConfigMock
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $consumerFactory,
            'consumerConfig',
            $this->consumerConfigMock
        );
        $consumerFactory->get(self::TEST_CONSUMER_NAME);
    }

    public function testConnectionInjectedForConsumer()
    {
        $consumerType = 'async';
        $consumerTypeValue = \Magento\Framework\MessageQueue\Model\TestConsumer::class;
        $consumers = [
            [
                'type' => [$consumerType => $consumerTypeValue]
            ]
        ];
        $consumerFactory = $this->getConsumerFactoryInstance($consumers);
        $consumerInstanceMock = $this->getMockBuilder($consumerTypeValue)
            ->getMock();
        $this->assertInstanceOf(get_class($consumerInstanceMock), $consumerFactory->get(self::TEST_CONSUMER_NAME));
    }

    /**
     * Return Consumer Factory with mocked objects
     *
     * @param array $consumers
     * @return ConsumerFactory
     */
    private function getConsumerFactoryInstance($consumers)
    {
        $consumerTypeValue = \Magento\Framework\MessageQueue\Model\TestConsumer::class;
        $handlerTypeValue = DataObject::class;
        $consumerType = 'async';

        /** @var ConsumerConfigItem|MockObject $consumerConfigItemMock */
        $consumerConfigItemMock = $this->getMockBuilder(ConsumerConfigItem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $consumerConfigItemMock->expects($this->any())->method('getName')->willReturn(self::TEST_CONSUMER_NAME);
        $consumerConfigItemMock->expects($this->any())->method('getQueue')->willReturn(self::TEST_CONSUMER_QUEUE);
        $consumerConfigItemMock->expects($this->any())->method('getConsumerInstance')->willReturn($consumerTypeValue);
        $consumerConfigItemMock->expects($this->any())->method('getHandlers')->willReturn([]);
        $this->consumerConfigMock->expects($this->any())
            ->method('getConsumer')
            ->with('test_consumer_name')
            ->willReturn($consumerConfigItemMock);
        $this->communicationConfigMock->expects($this->any())
            ->method('getTopics')
            ->willReturn(
                [
                    [
                        CommunicationConfig::TOPIC_NAME => 'topicName',
                        CommunicationConfig::TOPIC_IS_SYNCHRONOUS => false
                    ]
                ]
            );
        $this->communicationConfigMock->expects($this->any())
            ->method('getTopic')
            ->with('topicName')
            ->willReturn(
                [
                    CommunicationConfig::TOPIC_HANDLERS => [
                        [
                            CommunicationConfig::HANDLER_TYPE => $handlerTypeValue,
                            CommunicationConfig::HANDLER_METHOD => self::TEST_CONSUMER_METHOD
                        ]
                    ],
                ]
            );

        $consumerInstanceMock = $this->getMockBuilder($consumerTypeValue)
            ->getMock();
        $consumerMock = $this->getMockBuilder(ConsumerInterface::class)
            ->addMethods(['configure'])
            ->getMockForAbstractClass();

        $consumerConfigurationMock =
            $this->getMockBuilder(ConsumerConfigurationInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
        $consumerConfigurationMock->expects($this->any())->method('getType')->willReturn($consumerType);

        $objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();

        $objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnOnConsecutiveCalls($consumerMock, $consumerConfigurationMock, $consumerInstanceMock);

        $consumerFactory = $this->objectManager->getObject(
            ConsumerFactory::class,
            [
                'objectManager' => $objectManagerMock,
                'consumers' => $consumers
            ]
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $consumerFactory,
            'communicationConfig',
            $this->communicationConfigMock
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $consumerFactory,
            'consumerConfig',
            $this->consumerConfigMock
        );
        return $consumerFactory;
    }
}
