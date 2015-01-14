<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail;

class TransportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject
     */
    protected $_messageMock;

    /**
     * @var \Magento\Framework\Mail\Transport
     */
    protected $_transport;

    public function setUp()
    {
        $this->_messageMock = $this->getMock('\Magento\Framework\Mail\Message', [], [], '', false);
        $this->_transport = new \Magento\Framework\Mail\Transport($this->_messageMock);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The message should be an instance of \Zend_Mail
     */
    public function testTransportWithIncorrectMessageObject()
    {
        $this->_messageMock = $this->getMock('\Magento\Framework\Mail\MessageInterface');
        $this->_transport = new \Magento\Framework\Mail\Transport($this->_messageMock);
    }

    /**
     * @covers \Magento\Framework\Mail\Transport::sendMessage
     * @expectedException \Magento\Framework\Mail\Exception
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
