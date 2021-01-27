<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * Initiates JWKs for various encryption types.
 */
class JwkFactory
{
    /**
     * Create JWK for signatures generated with HMAC and SHA256
     *
     * @param string $key
     * @return Jwk
     */
    public function createHs256(string $key): Jwk
    {
        return $this->createHmac(256, $key);
    }

    /**
     * Create JWK for signatures generated with HMAC and SHA384
     *
     * @param string $key
     * @return Jwk
     */
    public function createHs384(string $key): Jwk
    {
        return $this->createHmac(384, $key);
    }

    /**
     * Create JWK for signatures generated with HMAC and SHA512
     *
     * @param string $key
     * @return Jwk
     */
    public function createHs512(string $key): Jwk
    {
        return $this->createHmac(512, $key);
    }

    private function createHmac(int $bits, string $key): Jwk
    {
        if (strlen($key) < 128) {
            throw new \InvalidArgumentException('Shared secret key must be at least 128 bits.');
        }

        return new Jwk(
            Jwk::KEY_TYPE_OCTET,
            ['k' => self::base64Encode($key)],
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            null,
            'HS' .$bits
        );
    }

    /**
     * Encode value into Base64Url format.
     *
     * @param string $value
     * @return string
     */
    private static function base64Encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * Decode Base64Url value.
     *
     * @param string $encoded
     * @return string
     */
    private static function base64Decode(string $encoded): string
    {
        $value = base64_decode(strtr($encoded, '-_', '+/'), true);
        if ($value === false) {
            throw new \InvalidArgumentException('Invalid base64Url string provided');
        }

        return $value;
    }
}
