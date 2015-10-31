<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\MessageQueue\Config\Converter as QueueConfigConverter;
use Magento\Framework\Exception\LocalizedException;

/**
 * Test class for Magento\Framework\MessageQueue\MessageEncoder
 */
class MessageEncoderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\MessageQueue\MessageEncoder */
    protected $encoder;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \Magento\Framework\MessageQueue\Config\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    /** @var \Magento\Framework\Webapi\ServiceOutputProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataObjectEncoderMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->configMock = $this->getMockBuilder('Magento\Framework\MessageQueue\Config\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataObjectEncoderMock = $this->getMockBuilder('Magento\Framework\Webapi\ServiceOutputProcessor')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->encoder = $this->objectManager->getObject(
            'Magento\Framework\MessageQueue\MessageEncoder',
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
        $exceptionMessage = 'Message with topic "customer.created" must be an instance of "Magento\Customer\Api\Data"';
        $this->configMock->expects($this->any())->method('get')->willReturn($this->getQueueConfigData());
        $object = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->dataObjectEncoderMock
            ->expects($this->once())
            ->method('convertValue')
            ->willThrowException(new LocalizedException(__($exceptionMessage)));

        $this->encoder->encode('customer.created', $object);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Message with topic "customer.created" must be an instance of "Magento\Customer\Api\Data
     */
    public function testEncodeInvalidMessageArray()
    {
        $exceptionMessage = 'Message with topic "customer.created" must be an instance of "Magento\Customer\Api\Data"';
        $this->configMock->expects($this->any())->method('get')->willReturn($this->getQueueConfigData());
        $object = $this->getMockBuilder('Magento\Customer\Api\Data\CustomerInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->dataObjectEncoderMock
            ->expects($this->once())
            ->method('convertValue')
            ->willThrowException(new LocalizedException(__($exceptionMessage)));

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
