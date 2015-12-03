<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\MessageQueue\Config\Converter as QueueConfigConverter;
use Magento\Framework\MessageQueue\ConsumerConfiguration;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConsumerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConsumerFactory
     */
    private $consumerFactory;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var QueueConfig
     */
    private $queueConfigMock;

    const TEST_CONSUMER_NAME = "test_consumer_name";
    const TEST_CONSUMER_CONNECTION = "test_consumer_connection";
    const TEST_CONSUMER_QUEUE = "test_consumer_queue";
    const TEST_CONSUMER_METHOD = "test_consumer_method";

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->queueConfigMock = $this->getMockBuilder('Magento\Framework\MessageQueue\Config\Data')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->consumerFactory = $this->objectManager->getObject(
            'Magento\Framework\MessageQueue\ConsumerFactory',
            [
                'queueConfig' => $this->queueConfigMock,
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage pecified consumer "test_consumer_name" is not declared.
     */
    public function testUndeclaredConsumerName()
    {
        $this->queueConfigMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::CONSUMERS => []
            ]));
        $this->consumerFactory->get(self::TEST_CONSUMER_NAME);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Could not find an implementation type for connection "test_consumer_connection".
     */
    public function testConsumerNotInjectedIntoClass()
    {
        $this->queueConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::CONSUMERS => [
                    self::TEST_CONSUMER_NAME => [
                        QueueConfigConverter::CONSUMER_CONNECTION => self::TEST_CONSUMER_CONNECTION
                    ]
                ],
            ]));
        $this->consumerFactory->get(self::TEST_CONSUMER_NAME);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Could not find an implementation type for connection "test_consumer_connection".
     */
    public function testNoConnectionInjectedForConsumer()
    {
        $this->queueConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::CONSUMERS => [
                    self::TEST_CONSUMER_NAME => [
                        QueueConfigConverter::CONSUMER_CONNECTION => self::TEST_CONSUMER_CONNECTION
                    ]
                ],
            ]));

        $consumerMock = $this->getMockBuilder('Magento\Framework\MessageQueue\ConsumerInterface')
            ->getMockForAbstractClass();

        $this->consumerFactory = $this->objectManager->getObject(
            'Magento\Framework\MessageQueue\ConsumerFactory',
            [
                'queueConfig' => $this->queueConfigMock,
                'consumers' => [
                    [
                        'type' => $consumerMock,
                        'connectionName' => 'randomPublisherConnection'
                    ]
                ]
            ]
        );

        $this->consumerFactory->get(self::TEST_CONSUMER_NAME);
    }

    public function testConnectionInjectedForConsumer()
    {
        $dispatchTypeName = 'Magento\Framework\DataObject';

        $this->queueConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::CONSUMERS => [
                    self::TEST_CONSUMER_NAME => [
                        QueueConfigConverter::CONSUMER_CONNECTION => self::TEST_CONSUMER_CONNECTION,
                        QueueConfigConverter::CONSUMER_NAME => self::TEST_CONSUMER_NAME,
                        QueueConfigConverter::CONSUMER_QUEUE => self::TEST_CONSUMER_QUEUE,
                        QueueConfigConverter::CONSUMER_CLASS => $dispatchTypeName,
                        QueueConfigConverter::CONSUMER_METHOD => self::TEST_CONSUMER_METHOD,
                    ]
                ],
            ]));

        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->setMethods(['create'])
            ->getMockForAbstractClass();

        $consumerTypeName = 'Magento\Framework\MessageQueue\Model\TestConsumer';
        $consumerMock = $this->getMockBuilder('Magento\Framework\MessageQueue\ConsumerInterface')
            ->setMethods(['configure'])
            ->getMockForAbstractClass();

        $objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with($consumerTypeName, [])
            ->will($this->returnValue($consumerMock));

        $dispatchInstanceMock = $this->getMockBuilder($dispatchTypeName)
            ->setMethods(['dispatch'])
            ->getMock();

        $objectManagerMock->expects($this->at(1))
            ->method('create')
            ->with($dispatchTypeName, [])
            ->will($this->returnValue($dispatchInstanceMock));

        $consumerConfigurationMock = $this->getMockBuilder('Magento\Framework\MessageQueue\ConsumerConfiguration')
            ->getMockForAbstractClass();

        $objectManagerMock->expects($this->at(2))
            ->method('create')
            ->with('Magento\Framework\MessageQueue\ConsumerConfiguration', ['data' => [
                ConsumerConfiguration::CONSUMER_NAME => self::TEST_CONSUMER_NAME,
                ConsumerConfiguration::QUEUE_NAME => self::TEST_CONSUMER_QUEUE,
                ConsumerConfiguration::HANDLERS => [
                    $dispatchInstanceMock,
                    self::TEST_CONSUMER_METHOD,
                ],
            ]])
            ->will($this->returnValue($consumerConfigurationMock));

        $this->consumerFactory = $this->objectManager->getObject(
            'Magento\Framework\MessageQueue\ConsumerFactory',
            [
                'queueConfig' => $this->queueConfigMock,
                'objectManager' => $objectManagerMock,
                'consumers' => [
                    [
                        'type' => $consumerTypeName,
                        'connectionName' => self::TEST_CONSUMER_CONNECTION,
                    ]
                ]
            ]
        );

        $this->assertSame($consumerMock, $this->consumerFactory->get(self::TEST_CONSUMER_NAME));
    }
}
