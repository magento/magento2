<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Payload;

class NestedPayload implements NestedPayloadInterface
{
    /**
     * @var string
     */
    private $token;

    /**
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @inheritDoc
     */
    public function getContent(): string
    {
        return $this->token;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): ?string
    {
        return self::CONTENT_TYPE;
    }
}
