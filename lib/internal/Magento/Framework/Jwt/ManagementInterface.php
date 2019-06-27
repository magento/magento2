<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * Provides an abstraction for JWT encode/decode/verification.
 */
interface ManagementInterface
{
    /**
     * Generates JWT in header.payload.signature format.
     *
     * @param array $claims
     * @return string
     * @throws \Exception
     */
    public function encode(array $claims): string;

    /**
     * Parses JWT and returns payload.
     *
     * @param string $token
     * @return array
     */
    public function decode(string $token): array;

    /**
     * Verifies JWT signature and claims.
     *
     * Throws \InvalidArgumentException if claims verification fails.
     *
     * @param string $token
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function verify(string $token): bool;
}
