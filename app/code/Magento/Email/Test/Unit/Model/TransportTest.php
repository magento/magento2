<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model;

use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\Mime\Message as SymfonyMessage;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Component\Mime\Header\Headers;
use Magento\Email\Model\Transport;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\EmailMessage;
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
     * @var MockObject|SymfonyMessage
     */
    private $symfonyMessageMock;

    /**
     * @var MockObject|EmailMessage
     */
    private $emailMessageMock;

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
     * @throws Exception
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
        $this->symfonyMessageMock = $this->createMock(SymfonyMessage::class);
        $headersMock = new Headers();
        $this->symfonyMessageMock->method('getHeaders')->willReturn($headersMock);
        $this->emailMessageMock = $this->getMockBuilder(EmailMessage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailMessageMock->method('getSymfonyMessage')->willReturn($this->symfonyMessageMock);

        $this->transport = new Transport(
            $this->emailMessageMock,
            $this->scopeConfigMock,
            $this->loggerMock
        );
    }

    /**
     * Verify exception is properly handled in case one occurred when message sent.
     *
     * @return void
     * @throws Exception
     * @throws \ReflectionException
     */
    public function testSendMessageBrokenMessage(): void
    {
        $exception = new RfcComplianceException('Email "" does not comply with addr-spec of RFC 2822.');
        $this->loggerMock->expects(self::once())->method('error')->with($exception);
        $this->expectException('Magento\Framework\Exception\MailException');
        $this->expectExceptionMessage('Unable to send mail. Please try again later.');

        $this->transport->sendMessage();
    }
}
