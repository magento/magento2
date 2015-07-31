<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Test\Unit;

use Magento\Framework\Amqp\Config\Data as QueueConfig;
use Magento\Framework\Amqp\Config\Converter as QueueConfigConverter;
use Magento\Framework\Amqp\ConsumerFactory;
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

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->queueConfigMock = $this->getMockBuilder('Magento\Framework\Amqp\Config\Data')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->consumerFactory = $this->objectManager->getObject(
            'Magento\Framework\Amqp\ConsumerFactory',
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

        $consumerMock = $this->getMockBuilder('Magento\Framework\Amqp\ConsumerInterface')
            ->getMockForAbstractClass();

        $this->consumerFactory = $this->objectManager->getObject(
            'Magento\Framework\Amqp\ConsumerFactory',
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
        $this->queueConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::CONSUMERS => [
                    self::TEST_CONSUMER_NAME => [
                        QueueConfigConverter::CONSUMER_CONNECTION => self::TEST_CONSUMER_CONNECTION
                    ]
                ],
            ]));

        $consumerMock = $this->getMockBuilder('Magento\Framework\Amqp\ConsumerInterface')
            ->getMockForAbstractClass();

        $this->consumerFactory = $this->objectManager->getObject(
            'Magento\Framework\Amqp\ConsumerFactory',
            [
                'queueConfig' => $this->queueConfigMock,
                'consumers' => [
                    [
                        'type' => $consumerMock,
                        'connectionName' => self::TEST_CONSUMER_CONNECTION
                    ]
                ]
            ]
        );

        $this->assertSame($consumerMock, $this->consumerFactory->get(self::TEST_CONSUMER_NAME));
    }
}
