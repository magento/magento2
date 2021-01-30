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

    /**
     * Create JWK to sign JWS with RSASSA-PKCS1-v1_5 using SHA-256.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @return Jwk
     */
    public function createSignRs256(string $privateKey, ?string $passPhrase): Jwk
    {
        return $this->createSignRsa(256, $privateKey, $passPhrase);
    }

    /**
     * Create JWK to verify JWS signed with RSASSA-PKCS1-v1_5 using SHA-256.
     *
     * @param string $publicKey
     * @return Jwk
     */
    public function createVerifyRs256(string $publicKey): Jwk
    {
        return $this->createVerifyRsa(256, $publicKey);
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

    private function createSignRsa(int $bits, string $key, ?string $pass): Jwk
    {
        $resource = openssl_get_privatekey($key, (string)$pass);
        $keyData = openssl_pkey_get_details($resource)['rsa'];
        openssl_free_key($resource);
        $keysMap = [
            'n' => 'n',
            'e' => 'e',
            'd' => 'd',
            'p' => 'p',
            'q' => 'q',
            'dp' => 'dmp1',
            'dq' => 'dmq1',
            'qi' => 'iqmp'
        ];
        $jwkData = [];
        foreach ($keysMap as $jwkKey => $rsaKey) {
            if (array_key_exists($rsaKey, $keyData)) {
                $jwkData[$jwkKey] = self::base64Encode($keyData[$rsaKey]);
            }
        }

        return new Jwk(
            Jwk::KEY_TYPE_RSA,
            $jwkData,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            null,
            'RS' .$bits
        );
    }

    private function createVerifyRsa(int $bits, string $key): Jwk
    {
        $resource = openssl_get_publickey($key);
        $keyData = openssl_pkey_get_details($resource)['rsa'];
        openssl_free_key($resource);
        $keysMap = [
            'n' => 'n',
            'e' => 'e'
        ];
        $jwkData = [];
        foreach ($keysMap as $jwkKey => $rsaKey) {
            if (array_key_exists($rsaKey, $keyData)) {
                $jwkData[$jwkKey] = self::base64Encode($keyData[$rsaKey]);
            }
        }

        return new Jwk(
            Jwk::KEY_TYPE_RSA,
            $jwkData,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            null,
            'RS' .$bits
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
