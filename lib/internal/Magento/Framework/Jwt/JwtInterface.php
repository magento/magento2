<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * JWT
 */
interface JwtInterface
{
    /**
     * Header.
     *
     * @return HeaderInterface
     */
    public function getHeader(): HeaderInterface;

    /**
     * Payload.
     *
     * @return PayloadInterface
     */
    public function getPayload(): PayloadInterface;
}
