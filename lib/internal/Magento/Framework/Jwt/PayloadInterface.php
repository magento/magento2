<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * JWT Payload.
 */
interface PayloadInterface
{
    /**
     * Payload's content.
     *
     * @return string
     */
    public function getContent(): string;

    /**
     * Payload type ("cty" header).
     *
     * @return string|null
     */
    public function getContentType(): ?string;
}
