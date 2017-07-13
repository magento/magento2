<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Test\Unit;

class TransportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework_MockObject
     */
    protected $_messageMock;

    /**
     * @var \Magento\Framework\Mail\Transport
     */
    protected $_transport;

    protected function setUp()
    {
        $this->_messageMock = $this->createMock(\Magento\Framework\Mail\Message::class);
        $this->_transport = new \Magento\Framework\Mail\Transport($this->_messageMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The message should be an instance of \Zend_Mail
     */
    public function testTransportWithIncorrectMessageObject()
    {
        $this->_messageMock = $this->createMock(\Magento\Framework\Mail\MessageInterface::class);
        $this->_transport = new \Magento\Framework\Mail\Transport($this->_messageMock);
    }

    /**
     * @covers \Magento\Framework\Mail\Transport::sendMessage
     * @expectedException \Magento\Framework\Exception\MailException
     * @expectedExceptionMessage No body specified
     */
    public function testSendMessageBrokenMessage()
    {
        $this->_messageMock->expects($this->any())
            ->method('getParts')
            ->will($this->returnValue(['a', 'b']));

        $this->_transport->sendMessage();
    }
}
