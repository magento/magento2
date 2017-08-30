<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mail\Test\Unit;

class TransportTest extends \PHPUnit\Framework\TestCase
{
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
