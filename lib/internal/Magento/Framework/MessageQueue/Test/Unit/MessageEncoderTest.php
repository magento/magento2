<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit;

use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\MessageEncoder;

/**
 * Test class for Magento\Framework\MessageQueue\MessageEncoder
 */
class MessageEncoderTest extends \PHPUnit\Framework\TestCase
{
    /** @var MessageEncoder */
    protected $encoder;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var CommunicationConfig|\PHPUnit\Framework\MockObject\MockObject */
    protected $communicationConfigMock;

    /** @var \Magento\Framework\Webapi\ServiceOutputProcessor|\PHPUnit\Framework\MockObject\MockObject */
    protected $dataObjectEncoderMock;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->dataObjectEncoderMock = $this->getMockBuilder(\Magento\Framework\Webapi\ServiceOutputProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->encoder = $this->objectManager->getObject(
            MessageEncoder::class,
            ['dataObjectEncoder' => $this->dataObjectEncoderMock]
        );
        $this->communicationConfigMock = $this->getMockBuilder(CommunicationConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->setBackwardCompatibleProperty(
            $this->encoder,
            'communicationConfig',
            $this->communicationConfigMock
        );
        parent::setUp();
    }

    /**
     */
    public function testEncodeInvalidTopic()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Specified topic "customer.created" is not declared.');

        $this->encoder->encode('customer.created', 'Some message');
    }

    /**
     */
    public function testDecodeInvalidTopic()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Specified topic "customer.created" is not declared.');

        $this->encoder->decode('customer.created', 'Some message');
    }

    /**
     */
    public function testEncodeInvalidMessage()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Message with topic "customer.created" must be an instance of "Magento\\Customer\\Api\\Data');

        $exceptionMessage = 'Message with topic "customer.created" must be an instance of "Magento\Customer\Api\Data"';
        $this->communicationConfigMock->expects($this->any())->method('getTopic')->willReturn(
            $this->getQueueConfigData()
        );
        $object = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
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
     */
    public function testEncodeInvalidMessageArray()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Message with topic "customer.created" must be an instance of "Magento\\Customer\\Api\\Data');

        $exceptionMessage = 'Message with topic "customer.created" must be an instance of "Magento\Customer\Api\Data"';
        $this->communicationConfigMock->expects($this->any())->method('getTopic')->willReturn(
            $this->getQueueConfigData()
        );
        $object = $this->getMockBuilder(\Magento\Customer\Api\Data\CustomerInterface::class)
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
     *
     * @return array
     */
    private function getQueueConfigData()
    {
        return [
            CommunicationConfig::TOPIC_REQUEST_TYPE => CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS,
            CommunicationConfig::TOPIC_REQUEST => \Magento\Customer\Api\Data\CustomerInterface::class
        ];
    }
}
