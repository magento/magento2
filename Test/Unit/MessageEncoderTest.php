<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Test\Unit;

use Magento\Framework\Amqp\Config\Converter as QueueConfigConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Test class for Magento\Framework\Amqp\MessageEncoder
 */
class MessageEncoderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Amqp\MessageEncoder */
    protected $encoder;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \Magento\Framework\Amqp\Config\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    /** @var \Magento\Framework\Webapi\ServiceOutputProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataObjectEncoderMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->configMock = $this->getMockBuilder('Magento\Framework\Amqp\Config\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectEncoderMock = $this->getMockBuilder('Magento\Framework\Webapi\ServiceOutputProcessor')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->encoder = $this->objectManager->getObject(
            'Magento\Framework\Amqp\MessageEncoder',
            [
                'queueConfig' => $this->configMock,
                'dataObjectEncoder' => $this->dataObjectEncoderMock
            ]
        );
        parent::setUp();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Specified topic "customer.created" is not declared.
     */
    public function testEncodeInvalidTopic()
    {
        $this->encoder->encode('customer.created', 'Some message');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Specified topic "customer.created" is not declared.
     */
    public function testDecodeInvalidTopic()
    {
        $this->encoder->decode('customer.created', 'Some message');
    }
    
    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Message with topic "customer.created" must be an instance of "Magento\Customer\Api\Data
     */
    public function testEncodeInvalidMessage()
    {
        $this->configMock->expects($this->any())->method('get')->willReturn($this->getQueueConfigData());
        $object = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->dataObjectEncoderMock
            ->expects($this->once())
            ->method('convertValue')
            ->willThrowException(new LocalizedException(new Phrase('')));

    $this->encoder->encode('customer.created', $object);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Message with topic "customer.created" must be an instance of "Magento\Customer\Api\Data
     */
    public function testEncodeInvalidMessageArray()
    {
        $this->configMock->expects($this->any())->method('get')->willReturn($this->getQueueConfigData());
        $object = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->dataObjectEncoderMock
            ->expects($this->once())
            ->method('convertValue')
            ->willThrowException(new LocalizedException(new Phrase('')));

        $this->encoder->encode('customer.created', [$object]);
    }

    /**
     * Data provider for queue config
     * @return array
     */
    private function getQueueConfigData()
    {
        return [
            QueueConfigConverter::TOPICS => [
                'customer.created' => [
                    QueueConfigConverter::TOPIC_SCHEMA => [
                        QueueConfigConverter::TOPIC_SCHEMA_TYPE => QueueConfigConverter::TOPIC_SCHEMA_TYPE_OBJECT,
                        QueueConfigConverter::TOPIC_SCHEMA_VALUE => 'Magento\Customer\Api\Data\CustomerInterface'
                    ]
                ]
            ]
        ];
    }
}
