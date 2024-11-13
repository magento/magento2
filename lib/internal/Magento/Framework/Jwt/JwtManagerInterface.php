<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

use Magento\Framework\Jwt\Exception\JwtException;

/**
 * Manages JWTs.
 */
interface JwtManagerInterface
{
    /**
     * Generate a token based on JWT data.
     *
     * @param JwtInterface $jwt
     * @param EncryptionSettingsInterface $encryption
     * @return string
     * @throws JwtException
     */
    public function create(JwtInterface $jwt, EncryptionSettingsInterface $encryption): string;

    /**
     * Read a JWT.
     *
     * @param string $token
     * @param EncryptionSettingsInterface[] $acceptableEncryption
     * @return JwtInterface
     * @throws JwtException
     */
    public function read(string $token, array $acceptableEncryption): JwtInterface;

    /**
     * Read unprotected headers.
     *
     * @param string $token
     * @return HeaderInterface[]
     */
    public function readHeaders(string $token): array;
}
