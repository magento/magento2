<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem;

class ConsumerFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /** @var CommunicationConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $communicationConfigMock;

    /** @var ConsumerConfig|\PHPUnit_Framework_MockObject_MockObject */
    protected $consumerConfigMock;

    const TEST_CONSUMER_NAME = "test_consumer_name";
    const TEST_CONSUMER_QUEUE = "test_consumer_queue";
    const TEST_CONSUMER_METHOD = "test_consumer_method";

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->communicationConfigMock = $this->getMockBuilder(CommunicationConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->consumerConfigMock = $this->getMockBuilder(ConsumerConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage pecified consumer "test_consumer_name" is not declared.
     */
    public function testUndeclaredConsumerName()
    {
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
        $consumerInstanceMock = $this->getMockBuilder($consumerTypeValue)->getMock();
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
        $handlerTypeValue = \Magento\Framework\DataObject::class;
        $consumerType = 'async';

        /** @var ConsumerConfigItem|\PHPUnit_Framework_MockObject_MockObject $consumerConfigItemMock */
        $consumerConfigItemMock = $this->getMockBuilder(ConsumerConfigItem::class)->disableOriginalConstructor()
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

        $consumerInstanceMock = $this->getMockBuilder($consumerTypeValue)->getMock();
        $consumerMock = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerInterface::class)
            ->setMethods(['configure'])
            ->getMockForAbstractClass();

        $consumerConfigurationMock =
            $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerConfigurationInterface::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
        $consumerConfigurationMock->expects($this->any())->method('getType')->willReturn($consumerType);

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->setMethods(['create'])
            ->getMockForAbstractClass();

        $objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnOnConsecutiveCalls($consumerMock, $consumerConfigurationMock, $consumerInstanceMock);

        $consumerFactory = $this->objectManager->getObject(
            \Magento\Framework\MessageQueue\ConsumerFactory::class,
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
