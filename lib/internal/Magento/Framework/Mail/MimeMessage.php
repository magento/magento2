<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mail;

use Laminas\Mime\Message as LaminasMimeMessage;

/**
 * Magento Framework Mime message
 */
class MimeMessage implements MimeMessageInterface
{
    /**
     * @var LaminasMimeMessage
     */
    private $mimeMessage;

    /**
     * MimeMessage constructor
     *
     * @param array $parts
     */
    public function __construct(array $parts)
    {
        $this->mimeMessage = new LaminasMimeMessage();
        $this->mimeMessage->setParts($parts);
    }

    /**
     * @inheritDoc
     */
    public function getParts(): array
    {
        return $this->mimeMessage->getParts();
    }

    /**
     * @inheritDoc
     */
    public function isMultiPart(): bool
    {
        return $this->mimeMessage->isMultiPart();
    }

    /**
     * @inheritDoc
     */
    public function getMessage(string $endOfLine = MimeInterface::LINE_END): string
    {
        return $this->mimeMessage->generateMessage($endOfLine);
    }

    /**
     * @inheritDoc
     */
    public function getPartHeadersAsArray(int $partNum): array
    {
        return $this->mimeMessage->getPartHeadersArray($partNum);
    }

    /**
     * @inheritDoc
     */
    public function getPartHeaders(int $partNum, string $endOfLine = MimeInterface::LINE_END): string
    {
        return $this->mimeMessage->getPartHeaders($partNum, $endOfLine);
    }

    /**
     * @inheritDoc
     */
    public function getPartContent(int $partNum, string $endOfLine = MimeInterface::LINE_END): string
    {
        return $this->mimeMessage->getPartContent($partNum, $endOfLine);
    }
}
