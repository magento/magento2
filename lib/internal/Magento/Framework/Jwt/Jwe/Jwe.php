<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Jwe;

use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\PayloadInterface;

/**
 * JWE DTO.
 */
class Jwe implements JweInterface
{
    /**
     * @var HeaderInterface
     */
    private $protectedHeader;

    /**
     * @var HeaderInterface|null
     */
    private $unprotectedHeader;

    /**
     * @var HeaderInterface[]|null
     */
    private $recipientHeaders;

    /**
     * @var PayloadInterface
     */
    private $payload;

    /**
     * @param HeaderInterface $protectedHeader
     * @param HeaderInterface|null $unprotectedHeader
     * @param HeaderInterface[]|null $recipientHeaders
     * @param PayloadInterface $payload
     */
    public function __construct(
        HeaderInterface $protectedHeader,
        ?HeaderInterface $unprotectedHeader,
        ?array $recipientHeaders,
        PayloadInterface $payload
    ) {
        $this->protectedHeader = $protectedHeader;
        $this->unprotectedHeader = $unprotectedHeader;
        $this->recipientHeaders = $recipientHeaders ? array_values($recipientHeaders) : null;
        $this->payload = $payload;
    }

    /**
     * @inheritDoc
     */
    public function getProtectedHeader(): HeaderInterface
    {
        return $this->protectedHeader;
    }

    /**
     * @inheritDoc
     */
    public function getSharedUnprotectedHeader(): ?HeaderInterface
    {
        return $this->unprotectedHeader;
    }

    /**
     * @inheritDoc
     */
    public function getPerRecipientUnprotectedHeaders(): ?array
    {
        return $this->recipientHeaders;
    }

    /**
     * @inheritDoc
     */
    public function getHeader(): HeaderInterface
    {
        return $this->getProtectedHeader();
    }

    /**
     * @inheritDoc
     */
    public function getPayload(): PayloadInterface
    {
        return $this->payload;
    }
}
