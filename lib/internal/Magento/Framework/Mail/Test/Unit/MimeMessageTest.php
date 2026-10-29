<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail\Test\Unit;

use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessage;
use Magento\Framework\Mail\MimePart;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address as SymfonyAddress;

/**
 * Unit tests for MimeMessage class
 *
 * @covers \Magento\Framework\Mail\MimeMessage
 */
class MimeMessageTest extends TestCase
{
    /**
     * Test body content
     */
    private const string BODY_CONTENT = '<p>Email Sent.</p>';

    /**
     * Test attachment content
     */
    private const string ATTACHMENT_CONTENT = "sku,qty,total\nTEST-SKU-1,3,29.97\n";

    /**
     * Test attachment file name
     */
    private const string ATTACHMENT_FILE_NAME = 'scheduled-report.csv';

    /**
     * Test attachment mime type
     */
    private const string ATTACHMENT_TYPE = 'text/csv';

    /**
     * Create an inline HTML body part
     *
     * @return MimePart
     */
    private function createBodyPart(): MimePart
    {
        return new MimePart(
            self::BODY_CONTENT,
            MimeInterface::TYPE_HTML,
            null,
            MimeInterface::DISPOSITION_INLINE,
            MimeInterface::ENCODING_QUOTED_PRINTABLE
        );
    }

    /**
     * Create an attachment part
     *
     * @param string $fileName
     * @return MimePart
     */
    private function createAttachmentPart(string $fileName = self::ATTACHMENT_FILE_NAME): MimePart
    {
        return new MimePart(
            self::ATTACHMENT_CONTENT,
            self::ATTACHMENT_TYPE,
            $fileName,
            MimeInterface::DISPOSITION_ATTACHMENT,
            MimeInterface::ENCODING_BASE64
        );
    }

    /**
     * Generate the raw MIME output, satisfying Symfony's mandatory "From" header requirement
     *
     * @param MimeMessage $mimeMessage
     * @return string
     */
    private function getRawMessage(MimeMessage $mimeMessage): string
    {
        $mimeMessage->getMimeMessage()->getHeaders()->addMailboxListHeader(
            'From',
            [new SymfonyAddress('no-reply@example.test')]
        );

        return $mimeMessage->getMessage();
    }

    /**
     * Test that a single body part with no attachments produces a single-part message
     *
     * @covers \Magento\Framework\Mail\MimeMessage::__construct
     * @covers \Magento\Framework\Mail\MimeMessage::getParts
     * @covers \Magento\Framework\Mail\MimeMessage::isMultiPart
     * @return void
     */
    public function testBodyOnlyProducesSinglePartMessage(): void
    {
        $mimeMessage = new MimeMessage([$this->createBodyPart()]);

        $this->assertFalse($mimeMessage->isMultiPart());
        $this->assertCount(1, $mimeMessage->getParts());
        $this->assertStringContainsString(self::BODY_CONTENT, $this->getRawMessage($mimeMessage));
    }

    /**
     * Test that an attachment alongside the body is preserved rather than dropped
     *
     * @covers \Magento\Framework\Mail\MimeMessage::__construct
     * @covers \Magento\Framework\Mail\MimeMessage::getParts
     * @covers \Magento\Framework\Mail\MimeMessage::isMultiPart
     * @covers \Magento\Framework\Mail\MimeMessage::getMessage
     * @return void
     */
    public function testBodyWithAttachmentPreservesBothParts(): void
    {
        $mimeMessage = new MimeMessage([$this->createBodyPart(), $this->createAttachmentPart()]);

        $this->assertTrue($mimeMessage->isMultiPart());
        $this->assertCount(2, $mimeMessage->getParts());

        $raw = $this->getRawMessage($mimeMessage);
        $this->assertStringContainsString(self::BODY_CONTENT, $raw);
        $this->assertStringContainsString(MimeInterface::MULTIPART_MIXED, $raw);
        $this->assertStringContainsString(
            'Content-Disposition: ' . MimeInterface::DISPOSITION_ATTACHMENT,
            $raw
        );
        $this->assertStringContainsString(self::ATTACHMENT_FILE_NAME, $raw);
    }

    /**
     * Test that every attachment survives when there is more than one
     *
     * @covers \Magento\Framework\Mail\MimeMessage::__construct
     * @covers \Magento\Framework\Mail\MimeMessage::getParts
     * @return void
     */
    public function testMultipleAttachmentsAreAllPreserved(): void
    {
        $mimeMessage = new MimeMessage(
            [
                $this->createBodyPart(),
                $this->createAttachmentPart('first.csv'),
                $this->createAttachmentPart('second.csv'),
            ]
        );

        $this->assertCount(3, $mimeMessage->getParts());

        $raw = $this->getRawMessage($mimeMessage);
        $this->assertStringContainsString('first.csv', $raw);
        $this->assertStringContainsString('second.csv', $raw);
    }

    /**
     * Test that an attachment is preserved even when no text body part is present
     *
     * @covers \Magento\Framework\Mail\MimeMessage::__construct
     * @return void
     */
    public function testAttachmentIsPreservedWithoutBodyPart(): void
    {
        $mimeMessage = new MimeMessage([$this->createAttachmentPart()]);

        $this->assertStringContainsString(self::ATTACHMENT_FILE_NAME, $this->getRawMessage($mimeMessage));
    }
}
