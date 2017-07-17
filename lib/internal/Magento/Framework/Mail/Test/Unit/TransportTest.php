<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Test\Unit;

class TransportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject
     */
    protected $messageMock;

    /**
     * @var \Magento\Framework\Mail\Transport
     */
    protected $transport;

    protected function setUp()
    {
        $this->messageMock = $this->getMock(\Magento\Framework\Mail\Message::class, [], [], '', false);
        $this->transport = new \Magento\Framework\Mail\Transport($this->messageMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The message should be an instance of \Zend_Mail
     */
    public function testTransportWithIncorrectMessageObject()
    {
        $this->messageMock = $this->getMock(\Magento\Framework\Mail\MessageInterface::class);
        $this->transport = new \Magento\Framework\Mail\Transport($this->messageMock);
    }

    /**
     * @covers \Magento\Framework\Mail\Transport::sendMessage
     * @expectedException \Magento\Framework\Exception\MailException
     * @expectedExceptionMessage No body specified
     */
    public function testSendMessageBrokenMessage()
    {
        $this->messageMock->expects($this->any())
            ->method('getParts')
            ->will($this->returnValue(['a', 'b']));

        $this->transport->sendMessage();
    }

    public function testGetMessage()
    {
        $this->assertSame($this->messageMock, $this->transport->getMessage());
    }
}
