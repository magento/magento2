<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail\Test\Unit;

use Magento\Framework\Mail\Address;
use Magento\Framework\Mail\AddressFactory;
use Magento\Framework\Mail\EmailMessage;
use Magento\Framework\Mail\MimeMessage;
use Magento\Framework\Mail\MimeMessageInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mime\Message as SymfonyMessage;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Part\TextPart;

/**
 * Unit tests for EmailMessage class
 *
 * @covers \Magento\Framework\Mail\EmailMessage
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailMessageTest extends TestCase
{
    /**
     * Test email constant
     */
    private const string EMAIL = 'test@example.com';

    /**
     * Test name constant
     */
    private const string NAME = 'Test Name';

    /**
     * Test subject constant
     */
    private const string SUBJECT = 'Test Subject';

    /**
     * Test body constant
     */
    private const string BODY = 'Test body content';

    /**
     * @var MimeMessageInterfaceFactory&MockObject
     */
    private MimeMessageInterfaceFactory|MockObject $mimeMessageFactory;

    /**
     * @var AddressFactory&MockObject
     */
    private AddressFactory|MockObject $addressFactory;

    /**
     * @var LoggerInterface&MockObject
     */
    private LoggerInterface|MockObject $logger;

    /**
     * Set up test dependencies
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->mimeMessageFactory = $this->createMock(MimeMessageInterfaceFactory::class);
        $this->addressFactory = $this->createMock(AddressFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->addressFactory->method('create')
            ->willReturnCallback(fn(array $args): Address => new Address($args['email'], $args['name']));
    }

    /**
     * Create EmailMessage instance with provided options
     *
     * @param array<string, mixed> $options
     * @return EmailMessage
     */
    private function createMessage(array $options = []): EmailMessage
    {
        $body = $options['body'] ?? self::BODY;
        $textPart = new TextPart($body, 'utf-8', 'plain');
        $symfonyMessage = $options['symfonyMessage'] ?? new SymfonyMessage(null, $textPart);

        $mock = $this->getMockBuilder(MimeMessage::class)
            ->onlyMethods(['getMimeMessage'])
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('getMimeMessage')->willReturn($symfonyMessage);

        return new EmailMessage(
            $mock,
            $options['to'] ?? [new Address(self::EMAIL, self::NAME)],
            $this->mimeMessageFactory,
            $this->addressFactory,
            $options['from'] ?? null,
            $options['cc'] ?? null,
            $options['bcc'] ?? null,
            $options['replyTo'] ?? null,
            $options['sender'] ?? null,
            $options['subject'] ?? '',
            'utf-8',
            $this->logger
        );
    }

    /**
     * Test basic construction and core getter methods
     *
     * @covers \Magento\Framework\Mail\EmailMessage::__construct
     * @covers \Magento\Framework\Mail\EmailMessage::getSymfonyMessage
     * @covers \Magento\Framework\Mail\EmailMessage::getHeaders
     * @covers \Magento\Framework\Mail\EmailMessage::getBodyText
     * @covers \Magento\Framework\Mail\EmailMessage::toString
     * @return void
     */
    public function testBasicConstruction(): void
    {
        $message = $this->createMessage([
            'subject' => self::SUBJECT,
            'from' => [new Address(self::EMAIL, self::NAME)]
        ]);

        $this->assertInstanceOf(SymfonyMessage::class, $message->getSymfonyMessage());
        $this->assertIsArray($message->getHeaders());
        $this->assertGreaterThan(0, count($message->getHeaders()));
        $this->assertStringContainsString(self::BODY, $message->getBodyText());
        $this->assertStringContainsString(self::SUBJECT, $message->toString());
    }

    /**
     * Test getMessageBody returns MimeMessageInterface created by factory
     *
     * @covers \Magento\Framework\Mail\EmailMessage::getMessageBody
     * @return void
     */
    public function testGetMessageBody(): void
    {
        $expected = $this->createMock(MimeMessageInterface::class);
        $this->mimeMessageFactory->expects($this->once())->method('create')->willReturn($expected);

        $this->assertSame($expected, $this->createMessage()->getMessageBody());
    }

    /**
     * Data provider for address getter tests
     *
     * @return array<string, array{string, string, bool}>
     */
    public static function addressDataProvider(): array
    {
        return [
            'to' => ['getTo', 'to', false],
            'from' => ['getFrom', 'from', true],
            'cc' => ['getCc', 'cc', true],
            'bcc' => ['getBcc', 'bcc', true],
            'replyTo' => ['getReplyTo', 'replyTo', true],
        ];
    }

    /**
     * Test address getter methods return correct Address arrays
     *
     * @dataProvider addressDataProvider
     * @covers \Magento\Framework\Mail\EmailMessage::getTo
     * @covers \Magento\Framework\Mail\EmailMessage::getFrom
     * @covers \Magento\Framework\Mail\EmailMessage::getCc
     * @covers \Magento\Framework\Mail\EmailMessage::getBcc
     * @covers \Magento\Framework\Mail\EmailMessage::getReplyTo
     * @param string $method
     * @param string $option
     * @param bool $nullableIfEmpty
     * @return void
     */
    #[DataProvider('addressDataProvider')]
    public function testAddressGetters(string $method, string $option, bool $nullableIfEmpty): void
    {
        $testEmail = 'test@test.com';
        $testName = 'Test';
        $options = $option === 'to'
            ? [$option => [new Address($testEmail, $testName)]]
            : ['to' => [new Address(self::EMAIL, self::NAME)], $option => [new Address($testEmail, $testName)]];

        $result = $this->createMessage($options)->$method();
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(Address::class, $result[0]);
        $this->assertSame($testEmail, $result[0]->getEmail());
        $this->assertSame($testName, $result[0]->getName());

        if ($nullableIfEmpty) {
            $this->assertNull($this->createMessage()->$method());
        }
    }

    /**
     * Test getSender returns Address when set and null when not set
     *
     * @covers \Magento\Framework\Mail\EmailMessage::getSender
     * @return void
     */
    public function testGetSender(): void
    {
        $message = $this->createMessage(['sender' => new Address(self::EMAIL, self::NAME)]);
        $sender = $message->getSender();
        $this->assertInstanceOf(Address::class, $sender);
        $this->assertSame(self::EMAIL, $sender->getEmail());

        $this->assertNull($this->createMessage()->getSender());
    }

    /**
     * Test getEncoding returns Content-Transfer-Encoding header value
     *
     * @covers \Magento\Framework\Mail\EmailMessage::getEncoding
     * @return void
     */
    public function testGetEncoding(): void
    {
        $textPart = new TextPart(self::BODY, 'utf-8', 'plain', 'quoted-printable');
        $symfonyMessage = new SymfonyMessage(null, $textPart);
        $symfonyMessage->getHeaders()->addTextHeader('Content-Transfer-Encoding', 'quoted-printable');

        $message = $this->createMessage(['symfonyMessage' => $symfonyMessage]);
        $this->assertSame('quoted-printable', $message->getEncoding());
    }

    /**
     * Data provider for address format tests
     *
     * @return array<string, array{array<int, Address|array<string, string>>}>
     */
    public static function addressFormatDataProvider(): array
    {
        return [
            'Address object' => [[new Address('test@test.com', 'Test Name')]],
            'array format' => [[['email' => 'test@test.com', 'name' => 'Test Name']]],
            'array without name' => [[['email' => 'test@test.com']]],
            'multiple addresses' => [[new Address('a@b.com', 'A'), new Address('c@d.com', 'C')]],
        ];
    }

    /**
     * Test various address input formats are handled correctly
     *
     * @dataProvider addressFormatDataProvider
     * @covers \Magento\Framework\Mail\EmailMessage::__construct
     * @covers \Magento\Framework\Mail\EmailMessage::getTo
     * @param array<int, Address|array<string, string>> $to
     * @return void
     */
    #[DataProvider('addressFormatDataProvider')]
    public function testAddressFormats(array $to): void
    {
        $message = $this->createMessage(['to' => $to]);
        $this->assertCount(count($to), $message->getTo());
    }

    /**
     * Test Address with null name is handled correctly
     *
     * @covers \Magento\Framework\Mail\EmailMessage::__construct
     * @return void
     */
    public function testAddressWithNullName(): void
    {
        $address = $this->createMock(Address::class);
        $address->method('getEmail')->willReturn(self::EMAIL);
        $address->method('getName')->willReturn(null);

        $message = $this->createMessage(['to' => [$address]]);
        $this->assertCount(1, $message->getTo());
    }

    /**
     * Data provider for subject handling edge cases
     *
     * @return array<string, array{string|null, bool}>
     */
    public static function subjectDataProvider(): array
    {
        return [
            'with subject' => [self::SUBJECT, true],
            'empty subject' => ['', false],
            'null subject' => [null, false],
        ];
    }

    /**
     * Test subject header is only added when subject is non-empty
     *
     * @dataProvider subjectDataProvider
     * @covers \Magento\Framework\Mail\EmailMessage::__construct
     * @covers \Magento\Framework\Mail\EmailMessage::getHeaders
     * @param string|null $subject
     * @param bool $shouldExist
     * @return void
     */
    #[DataProvider('subjectDataProvider')]
    public function testSubjectHandling(?string $subject, bool $shouldExist): void
    {
        $message = $this->createMessage(['subject' => $subject ?? '']);
        $headers = implode("\n", $message->getHeaders());

        if ($shouldExist) {
            $this->assertStringContainsString('Subject:', $headers);
        } else {
            $this->assertStringNotContainsString('Subject:', $headers);
        }
    }

    /**
     * Test invalid email address is logged and skipped
     *
     * @covers \Magento\Framework\Mail\EmailMessage::__construct
     * @return void
     */
    public function testInvalidEmailIsLoggedAndSkipped(): void
    {
        $this->logger->expects($this->atLeastOnce())
            ->method('warning')
            ->with($this->stringContains('Could not add an invalid email address'), $this->isArray());

        $message = $this->createMessage([
            'to' => [new Address(self::EMAIL, self::NAME), ['email' => 'invalid', 'name' => 'Bad']],
        ]);
        $this->assertInstanceOf(EmailMessage::class, $message);
    }

    /**
     * Test MIME-encoded email with spaces triggers sanitization and is logged
     *
     * @covers \Magento\Framework\Mail\EmailMessage::__construct
     * @return void
     */
    public function testEncodedEmailWithSpacesIsLoggedAndSkipped(): void
    {
        $this->logger->expects($this->atLeastOnce())
            ->method('warning')
            ->with($this->stringContains('Could not add an invalid email address'), $this->isArray());

        $encodedEmail = '=?UTF-8?B?' . base64_encode('test user@example.com') . '?=';
        $message = $this->createMessage([
            'to' => [new Address(self::EMAIL, self::NAME), new Address($encodedEmail, 'Invalid')],
        ]);
        $this->assertInstanceOf(EmailMessage::class, $message);
    }

    /**
     * Test valid MIME-encoded email passes sanitization
     *
     * @covers \Magento\Framework\Mail\EmailMessage::__construct
     * @return void
     */
    public function testValidEncodedEmailPasses(): void
    {
        $encodedEmail = '=?UTF-8?B?' . base64_encode('test@example.com') . '?=';
        $message = $this->createMessage(['to' => [new Address($encodedEmail, self::NAME)]]);
        $this->assertInstanceOf(EmailMessage::class, $message);
    }

    /**
     * Test getBodyText returns empty string when body is null
     *
     * @covers \Magento\Framework\Mail\EmailMessage::getBodyText
     * @return void
     */
    public function testGetBodyTextWithNullBody(): void
    {
        $message = $this->createMessage(['symfonyMessage' => new SymfonyMessage()]);
        $this->assertSame('', $message->getBodyText());
    }

    /**
     * Test getMessageBody returns empty parts array when body is not TextPart
     *
     * @covers \Magento\Framework\Mail\EmailMessage::getMessageBody
     * @return void
     */
    public function testGetMessageBodyWithNonTextPart(): void
    {
        $nonTextPart = $this->createMock(AbstractPart::class);
        $symfonyMessage = new SymfonyMessage(null, $nonTextPart);

        $expected = $this->createMock(MimeMessageInterface::class);
        $this->mimeMessageFactory->expects($this->once())
            ->method('create')
            ->with(['parts' => []])
            ->willReturn($expected);

        $message = $this->createMessage(['symfonyMessage' => $symfonyMessage]);
        $this->assertSame($expected, $message->getMessageBody());
    }

    /**
     * Test empty 'to' recipients does not throw exception due to bug
     *
     * @covers \Magento\Framework\Mail\EmailMessage::__construct
     * @covers \Magento\Framework\Mail\EmailMessage::getTo
     * @return void
     */
    public function testEmptyToRecipientsDoesNotThrow(): void
    {
        $message = $this->createMessage(['to' => []]);
        $this->assertSame([], $message->getTo());
    }

    /**
     * Test all address types work correctly when used together
     *
     * @covers \Magento\Framework\Mail\EmailMessage::__construct
     * @covers \Magento\Framework\Mail\EmailMessage::getTo
     * @covers \Magento\Framework\Mail\EmailMessage::getFrom
     * @covers \Magento\Framework\Mail\EmailMessage::getCc
     * @covers \Magento\Framework\Mail\EmailMessage::getBcc
     * @covers \Magento\Framework\Mail\EmailMessage::getReplyTo
     * @covers \Magento\Framework\Mail\EmailMessage::getSender
     * @return void
     */
    public function testAllAddressTypesTogether(): void
    {
        $address = fn(string $prefix): Address => new Address("$prefix@test.com", ucfirst($prefix));

        $message = $this->createMessage([
            'from' => [$address('from')],
            'cc' => [$address('cc')],
            'bcc' => [$address('bcc')],
            'replyTo' => [$address('reply')],
            'sender' => $address('sender'),
            'subject' => self::SUBJECT,
        ]);

        // Verify To address
        $this->assertCount(1, $message->getTo());
        $this->assertInstanceOf(Address::class, $message->getTo()[0]);
        $this->assertSame(self::EMAIL, $message->getTo()[0]->getEmail());

        // Verify From address
        $this->assertCount(1, $message->getFrom());
        $this->assertInstanceOf(Address::class, $message->getFrom()[0]);
        $this->assertSame('from@test.com', $message->getFrom()[0]->getEmail());

        // Verify Cc address
        $this->assertCount(1, $message->getCc());
        $this->assertInstanceOf(Address::class, $message->getCc()[0]);
        $this->assertSame('cc@test.com', $message->getCc()[0]->getEmail());

        // Verify Bcc address
        $this->assertCount(1, $message->getBcc());
        $this->assertInstanceOf(Address::class, $message->getBcc()[0]);
        $this->assertSame('bcc@test.com', $message->getBcc()[0]->getEmail());

        // Verify ReplyTo address
        $this->assertCount(1, $message->getReplyTo());
        $this->assertInstanceOf(Address::class, $message->getReplyTo()[0]);
        $this->assertSame('reply@test.com', $message->getReplyTo()[0]->getEmail());

        // Verify Sender address
        $this->assertInstanceOf(Address::class, $message->getSender());
        $this->assertSame('sender@test.com', $message->getSender()->getEmail());
        $this->assertSame('Sender', $message->getSender()->getName());
    }
}
