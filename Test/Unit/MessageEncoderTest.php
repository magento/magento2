<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Test\Unit;

use Magento\Framework\Object;

class MessageEncoderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Amqp\MessageEncoder */
    protected $encoder;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var \Magento\Framework\Amqp\Config\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->configMock = $this->getMockBuilder('Magento\Framework\Amqp\Config\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->encoder = $this->objectManager->getObject(
            'Magento\Framework\Amqp\MessageEncoder',
            ['queueConfig' => $this->configMock]
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
        $this->configMock
            ->expects($this->any())
            ->method('get')
            ->willReturn(
                [
                    'topics' => [
                        'customer.created' => [
                            'schema' => 'Magento\Customer\Api\Data\CustomerInterface'
                        ]
                    ]
                ]
            );
        $this->encoder->encode('customer.created', new Object());
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Message with topic "customer.created" must be an instance of "SomeType[]"
     */
    public function testEncodeInvalidMessageArray()
    {
        $this->configMock
            ->expects($this->any())
            ->method('get')
            ->willReturn(
                [
                    'topics' => [
                        'customer.created' => [
                            'schema' => 'SomeType[]'
                        ]
                    ]
                ]
            );
        $this->encoder->encode('customer.created', [new Object()]);
    }
}