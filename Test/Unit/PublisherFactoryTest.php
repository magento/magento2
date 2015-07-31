<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Test\Unit;

use Magento\Framework\Amqp\Config\Data as QueueConfig;
use Magento\Framework\Amqp\Config\Converter as QueueConfigConverter;
use Magento\Framework\Amqp\PublisherFactory;
use Magento\Framework\ObjectManager\Helper\Composite as CompositeHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class PublisherFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PublisherFactory
     */
    private $producerFactory;

    /**
     * @var CompositeHelper
     */
    private $compositeHelperMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var QueueConfig
     */
    private $queueConfigMock;

    /**
     * @var string
     */
    const TEST_TOPIC = "test_topic";

    /**
     * @var string
     */
    const TEST_PUBLISHER = "test_publisher";

    /**
     * @var string
     */
    const TEST_PUBLISHER_CONNECTION = "test_publisher_connection";

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->queueConfigMock = $this->getMockBuilder('Magento\Framework\Amqp\Config\Data')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $this->compositeHelperMock = $this->getMockBuilder('Magento\Framework\ObjectManager\Helper\Composite')
            ->disableOriginalConstructor()
            ->setMethods(['filterAndSortDeclaredComponents'])
            ->getMock();
        $this->compositeHelperMock
            ->expects($this->any())
            ->method('filterAndSortDeclaredComponents')
            ->will($this->returnArgument(0));
        $this->producerFactory = $this->objectManager->getObject(
            'Magento\Framework\Amqp\PublisherFactory',
            [
                'queueConfig' => $this->queueConfigMock,
                'compositeHelper' => $this->compositeHelperMock
            ]
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Specified topic "test_topic" is not declared.
     */
    public function testUndeclaredTopic()
    {
        $this->queueConfigMock->expects($this->once())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::TOPICS => []
            ]));
        $this->producerFactory->create(self::TEST_TOPIC);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Specified publisher "test_publisher" is not declared.
     */
    public function testUndeclaredPublisher()
    {
        $this->queueConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::TOPICS => [
                    self::TEST_TOPIC => [
                        QueueConfigConverter::TOPIC_PUBLISHER => self::TEST_PUBLISHER
                    ]
                ],
                QueueConfigConverter::PUBLISHERS => []
            ]));
        $this->producerFactory->create(self::TEST_TOPIC);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Could not find an implementation type for connection "test_publisher_connection".
     */
    public function testPublisherNotInjectedIntoClass()
    {
        $this->queueConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::TOPICS => [
                    self::TEST_TOPIC => [
                        QueueConfigConverter::TOPIC_PUBLISHER => self::TEST_PUBLISHER
                    ]
                ],
                QueueConfigConverter::PUBLISHERS => [
                    self::TEST_PUBLISHER => [
                        QueueConfigConverter::PUBLISHER_CONNECTION => self::TEST_PUBLISHER_CONNECTION
                    ]
                ]
            ]));
        $this->producerFactory->create(self::TEST_TOPIC);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Could not find an implementation type for connection "test_publisher_connection".
     */
    public function testNoPublishersForConnection()
    {
        $this->queueConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::TOPICS => [
                    self::TEST_TOPIC => [
                        QueueConfigConverter::TOPIC_PUBLISHER => self::TEST_PUBLISHER
                    ]
                ],
                QueueConfigConverter::PUBLISHERS => [
                    self::TEST_PUBLISHER => [
                        QueueConfigConverter::PUBLISHER_CONNECTION => self::TEST_PUBLISHER_CONNECTION
                    ]
                ]
            ]));


        $publisherMock = $this->getMockBuilder('Magento\Framework\Amqp\PublisherInterface')
            ->getMockForAbstractClass();

        $this->producerFactory = $this->objectManager->getObject(
            'Magento\Framework\Amqp\PublisherFactory',
            [
                'queueConfig' => $this->queueConfigMock,
                'compositeHelper' => $this->compositeHelperMock,
                'publishers' => [
                    [
                        'type' => $publisherMock,
                        'sortOrder' => 10,
                        'connectionName' => 'randomPublisherConnection'
                    ]
                ]
            ]
        );

        $this->assertSame($publisherMock, $this->producerFactory->create(self::TEST_TOPIC));
    }

    public function testPublisherReturned()
    {
        $this->queueConfigMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue([
                QueueConfigConverter::TOPICS => [
                    self::TEST_TOPIC => [
                        QueueConfigConverter::TOPIC_PUBLISHER => self::TEST_PUBLISHER
                    ]
                ],
                QueueConfigConverter::PUBLISHERS => [
                    self::TEST_PUBLISHER => [
                        QueueConfigConverter::PUBLISHER_CONNECTION => self::TEST_PUBLISHER_CONNECTION
                    ]
                ]
            ]));


        $publisherMock = $this->getMockBuilder('Magento\Framework\Amqp\PublisherInterface')
            ->getMockForAbstractClass();

        $this->producerFactory = $this->objectManager->getObject(
            'Magento\Framework\Amqp\PublisherFactory',
            [
                'queueConfig' => $this->queueConfigMock,
                'compositeHelper' => $this->compositeHelperMock,
                'publishers' => [
                    [
                        'type' => $publisherMock,
                        'sortOrder' => 10,
                        'connectionName' => self::TEST_PUBLISHER_CONNECTION
                    ]
                ]
            ]
        );

        $this->assertSame($publisherMock, $this->producerFactory->create(self::TEST_TOPIC));
    }
}
