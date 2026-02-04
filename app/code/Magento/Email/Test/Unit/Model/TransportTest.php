<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model;

use Magento\Email\Model\Transport;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\EmailMessage;
use Magento\Framework\Mail\EmailMessageInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\TransportInterface as SymfonyTransportInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Message as SymfonyMessage;

/**
 * Unit tests for email transport functionality.
 *
 * @coversDefaultClass \Magento\Email\Model\Transport
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransportTest extends TestCase
{
    /**
     * Logger mock instance.
     *
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * Symfony message mock instance.
     *
     * @var SymfonyMessage|MockObject
     */
    private $symfonyMessageMock;

    /**
     * Email message mock instance.
     *
     * @var EmailMessage|MockObject
     */
    private $emailMessageMock;

    /**
     * Transport instance under test.
     *
     * @var Transport
     */
    private $transport;

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->symfonyMessageMock = $this->createMock(SymfonyMessage::class);
        $this->symfonyMessageMock->expects($this->any())
            ->method('getHeaders')
            ->willReturn(new Headers());
        $this->emailMessageMock = $this->getMockBuilder(EmailMessage::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailMessageMock->expects($this->any())
            ->method('getSymfonyMessage')
            ->willReturn($this->symfonyMessageMock);

        $this->transport = new Transport(
            $this->emailMessageMock,
            $this->createMock(ScopeConfigInterface::class),
            $this->loggerMock
        );
    }

    /**
     * Create a test email with standard test data.
     *
     * @param string|null $fromEmail
     * @return Email
     */
    private function createTestEmail(?string $fromEmail = 'sender@example.com'): Email
    {
        $email = new Email();
        if ($fromEmail) {
            $email->from($fromEmail);
        }
        return $email->to('recipient@example.com')->subject('Test')->text('Test body');
    }

    /**
     * Create Transport instance with given config overrides.
     *
     * @param array $config
     * @return Transport
     */
    private function createTransport(array $config = []): Transport
    {
        $defaults = [
            Transport::XML_PATH_SENDING_SET_RETURN_PATH => '0',
            Transport::XML_PATH_SENDING_RETURN_PATH_EMAIL => null,
            'system/smtp/transport' => 'sendmail',
            'system/smtp/host' => 'smtp.example.com',
            'system/smtp/port' => '587',
            'system/smtp/username' => 'user@example.com',
            'system/smtp/password' => 'password123',
            'system/smtp/auth' => 'login',
            'system/smtp/ssl' => 'tls',
        ];
        $config = array_merge($defaults, $config);

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnCallback(fn($path) => $config[$path] ?? null);

        return new Transport($this->createMock(EmailMessage::class), $scopeConfig, $this->loggerMock);
    }

    /**
     * Verify exception is properly handled when message sent fails due to RFC compliance.
     *
     * @return void
     * @covers ::sendMessage
     */
    public function testSendMessageBrokenMessage(): void
    {
        $exception = new RfcComplianceException('Email "" does not comply with addr-spec of RFC 2822.');
        $this->loggerMock->expects(self::once())->method('error')->with($exception);
        $this->expectException(MailException::class);
        $this->expectExceptionMessage('Unable to send mail. Please try again later.');

        $this->transport->sendMessage();
    }

    /**
     * Data provider for setReturnPath tests.
     *
     * @return array
     */
    public static function setReturnPathDataProvider(): array
    {
        return [
            'custom return path (mode=2)' => ['2', 'return@example.com', 'sender@example.com', 'return@example.com'],
            'from address (mode=1)' => ['1', null, 'sender@example.com', 'sender@example.com'],
            'no sender (mode=1, no from)' => ['1', null, null, null],
            'disabled (mode=0)' => ['0', null, 'sender@example.com', null],
        ];
    }

    /**
     * Test setReturnPath behavior with various configurations.
     *
     * @param string $isSetReturnPath
     * @param string|null $returnPathEmail
     * @param string|null $fromEmail
     * @param string|null $expectedSender
     * @return void
     * @dataProvider setReturnPathDataProvider
     * @covers ::__construct
     * @covers ::setReturnPath
     */
    public function testSetReturnPath(
        string $isSetReturnPath,
        ?string $returnPathEmail,
        ?string $fromEmail,
        ?string $expectedSender
    ): void {
        $email = $this->createTestEmail($fromEmail);

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnCallback(fn($path) => match ($path) {
                Transport::XML_PATH_SENDING_SET_RETURN_PATH => $isSetReturnPath,
                Transport::XML_PATH_SENDING_RETURN_PATH_EMAIL => $returnPathEmail,
                default => null
            });

        $transport = new Transport($this->createMock(EmailMessage::class), $scopeConfig, $this->loggerMock);
        $method = (new \ReflectionClass($transport))->getMethod('setReturnPath');
        $method->invoke($transport, $email);

        $senderHeader = $email->getHeaders()->get('Sender');
        if ($expectedSender === null) {
            $this->assertNull($senderHeader, 'Sender header should not be set');
        } else {
            $this->assertNotNull($senderHeader, 'Sender header should be set');
            $this->assertStringContainsString($expectedSender, $senderHeader->getBodyAsString());
        }
    }

    /**
     * Test getMessage returns the injected email message instance.
     *
     * @return void
     * @covers ::__construct
     * @covers ::getMessage
     */
    public function testGetMessageReturnsEmailMessage(): void
    {
        $this->assertInstanceOf(EmailMessageInterface::class, $this->transport->getMessage());
    }

    /**
     * Data provider for transport type tests.
     *
     * @return array
     */
    public static function transportTypeDataProvider(): array
    {
        return [
            'SMTP' => ['smtp', EsmtpTransport::class],
            'Sendmail' => ['sendmail', SymfonyTransportInterface::class],
            'Null defaults to Sendmail' => [null, SymfonyTransportInterface::class],
        ];
    }

    /**
     * Test getTransport returns correct transport type based on configuration.
     *
     * @param string|null $transportType
     * @param string $expectedClass
     * @return void
     * @dataProvider transportTypeDataProvider
     * @covers ::getTransport
     * @covers ::createSmtpTransport
     * @covers ::createSendmailTransport
     */
    public function testGetTransportReturnsCorrectType(?string $transportType, string $expectedClass): void
    {
        $this->assertInstanceOf($expectedClass, $this->createTransport([
            'system/smtp/transport' => $transportType
        ])->getTransport());
    }

    /**
     * Test getTransport caches the transport instance for subsequent calls.
     *
     * @return void
     * @covers ::getTransport
     */
    public function testGetTransportCachesTransportInstance(): void
    {
        $transport = $this->createTransport();
        $this->assertSame($transport->getTransport(), $transport->getTransport());
    }

    /**
     * Data provider for SMTP configurations including SSL, auth combinations and edge cases.
     *
     * @return array
     */
    public static function smtpConfigDataProvider(): array
    {
        $configs = [];

        // SSL + auth combinations
        foreach (['tls', 'ssl', '', null] as $ssl) {
            foreach (['login', 'plain', 'none'] as $auth) {
                $sslLabel = $ssl === null ? 'null' : ($ssl ?: 'none');
                $configs["SSL={$sslLabel}, auth={$auth}"] = [
                    'host' => 'smtp.example.com',
                    'port' => '587',
                    'username' => 'user@example.com',
                    'password' => 'password',
                    'ssl' => $ssl,
                    'auth' => $auth,
                ];
            }
        }

        $edgeCases = [
            'empty host' => ['host' => '', 'port' => '587'],
            'zero port' => ['host' => 'smtp.example.com', 'port' => '0'],
            'empty port' => ['host' => 'smtp.example.com', 'port' => ''],
            'null port' => ['host' => 'smtp.example.com', 'port' => null],
            'empty username' => ['host' => 'smtp.example.com', 'port' => '587', 'username' => ''],
            'null username' => ['host' => 'smtp.example.com', 'port' => '587', 'username' => null],
            'empty password' => ['host' => 'smtp.example.com', 'port' => '587', 'password' => ''],
            'null password' => ['host' => 'smtp.example.com', 'port' => '587', 'password' => null],
            'all credentials empty' =>
                ['host' => 'smtp.example.com', 'port' => '587', 'username' => '', 'password' => ''],
            'all credentials null' =>
                ['host' => 'smtp.example.com', 'port' => '587', 'username' => null, 'password' => null],
        ];

        $edgeCaseDefaults = [
            'host' => 'smtp.example.com',
            'port' => '587',
            'username' => 'user@example.com',
            'password' => 'password',
            'ssl' => '',
            'auth' => 'none',
        ];
        foreach ($edgeCases as $name => $overrides) {
            $configs[$name] = [...$edgeCaseDefaults, ...$overrides];
        }

        return $configs;
    }

    /**
     * Test createSmtpTransport with various SSL, auth, and edge case configurations.
     *
     * @param string $host
     * @param string|null $port
     * @param string|null $username
     * @param string|null $password
     * @param string|null $ssl
     * @param string $auth
     * @return void
     * @dataProvider smtpConfigDataProvider
     * @covers ::getTransport
     * @covers ::createSmtpTransport
     */
    public function testCreateSmtpTransport(
        string $host,
        ?string $port,
        ?string $username,
        ?string $password,
        ?string $ssl,
        string $auth
    ): void {
        $this->assertInstanceOf(EsmtpTransport::class, $this->createTransport([
            'system/smtp/transport' => 'smtp',
            'system/smtp/host' => $host,
            'system/smtp/port' => $port,
            'system/smtp/username' => $username,
            'system/smtp/password' => $password,
            'system/smtp/ssl' => $ssl,
            'system/smtp/auth' => $auth,
        ])->getTransport());
    }

    /**
     * Data provider for SMTP exception scenarios.
     *
     * @return array
     */
    public static function smtpExceptionDataProvider(): array
    {
        return [
            'null host' => [
                ['system/smtp/host' => null, 'system/smtp/auth' => 'none'],
                \TypeError::class,
                null,
            ],
            'invalid auth' => [
                ['system/smtp/auth' => 'invalid_auth_type'],
                \InvalidArgumentException::class,
                'Invalid authentication type: invalid_auth_type',
            ],
            'null auth' => [
                ['system/smtp/auth' => null],
                \InvalidArgumentException::class,
                'Invalid authentication type:',
            ],
            'empty auth' => [
                ['system/smtp/auth' => ''],
                \InvalidArgumentException::class,
                'Invalid authentication type:',
            ],
        ];
    }

    /**
     * Test createSmtpTransport throws appropriate exceptions for invalid configurations.
     *
     * @param array $config
     * @param string $expectedException
     * @param string|null $expectedMessage
     * @return void
     * @dataProvider smtpExceptionDataProvider
     * @covers ::getTransport
     * @covers ::createSmtpTransport
     */
    public function testCreateSmtpTransportThrowsException(
        array $config,
        string $expectedException,
        ?string $expectedMessage
    ): void {
        $this->expectException($expectedException);
        if ($expectedMessage) {
            $this->expectExceptionMessage($expectedMessage);
        }

        $this->createTransport(array_merge(['system/smtp/transport' => 'smtp'], $config))->getTransport();
    }

    /**
     * Test sendMessage logs transport exception and throws MailException.
     *
     * @return void
     * @covers ::sendMessage
     */
    public function testSendMessageLogsTransportExceptionAndThrowsMailException(): void
    {
        $emailMessageMock = $this->createMock(EmailMessage::class);
        $emailMessageMock->expects($this->once())
            ->method('getSymfonyMessage')
            ->willReturn($this->createTestEmail());

        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Transport error while sending email:'));

        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnCallback(fn($path) => match ($path) {
                'system/smtp/transport' => 'smtp',
                'system/smtp/host' => 'invalid.host.example',
                'system/smtp/port' => '587',
                'system/smtp/auth' => 'none',
                'system/smtp/ssl' => '',
                Transport::XML_PATH_SENDING_SET_RETURN_PATH => '0',
                default => null
            });

        $transport = new Transport($emailMessageMock, $scopeConfig, $loggerMock);

        $this->expectException(MailException::class);
        $this->expectExceptionMessage('Transport error: Unable to send mail at this time.');

        $transport->sendMessage();
    }
}
