<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Test\Unit;

use Zend\Mail\Headers;

class TransportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The message should be an instance of \Zend\Mail\Message
     */
    public function testTransportWithIncorrectMessageObject()
    {
        new \Magento\Framework\Mail\Transport(
            $this->getMock(\Magento\Framework\Mail\MessageInterface::class)
        );
    }

    /**
     * @covers \Magento\Framework\Mail\Transport::sendMessage
     * @expectedException \Magento\Framework\Exception\MailException
     * @expectedExceptionMessage Invalid email; contains no "To" header
     */
    public function testSendMessageBrokenMessage()
    {
        $transport = new \Magento\Framework\Mail\Transport(
            new \Magento\Framework\Mail\Message()
        );

        $transport->sendMessage();
    }
}
