<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model;

use Laminas\Mail\Transport\Exception\RuntimeException;
use Magento\Email\Model\Transport;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Message;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for email transport functionality.
 */
class TransportTest extends TestCase
{
    /**
     * @var MockObject|LoggerInterface
     */
    private $loggerMock;

    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['error'])
            ->getMockForAbstractClass();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->transport = new Transport(
            new Message(),
            $this->scopeConfigMock,
            null,
            $this->loggerMock
        );
    }

    /**
     * Verify exception is properly handled in case one occurred when message sent.
     *
     * @return void
     */
    public function testSendMessageBrokenMessage(): void
    {
        $exception = new RuntimeException('Invalid email; contains no at least one of "To", "Cc", and "Bcc" header');
        $this->loggerMock->expects(self::once())->method('error')->with($exception);
        $this->expectException('Magento\Framework\Exception\MailException');
        $this->expectExceptionMessage('Unable to send mail. Please try again later.');

        $this->transport->sendMessage();
    }
}
