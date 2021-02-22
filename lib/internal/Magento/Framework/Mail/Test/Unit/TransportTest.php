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
     */
    public function testSendMessageBrokenMessage()
    {
        $this->expectException(\Magento\Framework\Exception\MailException::class);
        $this->expectExceptionMessage('Invalid email; contains no at least one of "To", "Cc", and "Bcc" header');

        $transport = new \Magento\Framework\Mail\Transport(
            new \Magento\Framework\Mail\Message()
        );

        $transport->sendMessage();
    }
}
