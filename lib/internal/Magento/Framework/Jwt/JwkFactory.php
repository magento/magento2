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
    private const EC_CURVE_MAP = [
        '1.2.840.10045.3.1.7' => ['name' => 'P-256', 'bits' => 256],
        '1.3.132.0.34' => ['name' => 'P-384', 'bits' => 384],
        '1.3.132.0.35' => ['name' => 'P-521', 'bits' => 512]
    ];

    /**
     * Create JWK object from key data.
     *
     * @param array $data
     * @return Jwk
     */
    public function createFromData(array $data): Jwk
    {
        if (!array_key_exists('kty', $data)) {
            throw new \InvalidArgumentException('Missing key type in JWK data (kty)');
        }
        $kty = $data['kty'];
        unset($data['kty']);
        $use = array_key_exists('use', $data) ? $data['use'] : null;
        unset($data['use']);
        $keyOps = array_key_exists('key_ops', $data) ? $data['key_ops'] : null;
        unset($data['key_ops']);
        $alg = array_key_exists('alg', $data) ? $data['alg'] : null;
        unset($data['alg']);
        $x5u = array_key_exists('x5u', $data) ? $data['x5u'] : null;
        unset($data['use']);
        $x5c = array_key_exists('x5c', $data) ? $data['x5c'] : null;
        unset($data['x5c']);
        $x5t = array_key_exists('x5t', $data) ? $data['x5t'] : null;
        unset($data['x5t']);
        $x5tS256 = array_key_exists('x5t#S256', $data) ? $data['x5t#S256'] : null;
        unset($data['x5t#S256']);
        $kid = array_key_exists('kid', $data) ? $data['kid'] : null;
        unset($data['kid']);

        return new Jwk(
            $kty,
            $data,
            $use,
            $keyOps,
            $alg,
            $x5u,
            $x5c,
            $x5t,
            $x5tS256,
            $kid
        );
    }

    /**
     * Create JWK for signatures generated with HMAC and SHA256
     *
     * @param string $key
     * @return Jwk
     */
    public function createHs256(string $key): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_HS256);
    }

    /**
     * Create JWK for signatures generated with HMAC and SHA384
     *
     * @param string $key
     * @return Jwk
     */
    public function createHs384(string $key): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_HS384);
    }

    /**
     * Create JWK for signatures generated with HMAC and SHA512
     *
     * @param string $key
     * @return Jwk
     */
    public function createHs512(string $key): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_HS512);
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

    /**
     * Create JWK to sign JWS with RSASSA-PKCS1-v1_5 using SHA-384.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @return Jwk
     */
    public function createSignRs384(string $privateKey, ?string $passPhrase): Jwk
    {
        return $this->createSignRsa(384, $privateKey, $passPhrase);
    }

    /**
     * Create JWK to verify JWS signed with RSASSA-PKCS1-v1_5 using SHA-384.
     *
     * @param string $publicKey
     * @return Jwk
     */
    public function createVerifyRs384(string $publicKey): Jwk
    {
        return $this->createVerifyRsa(384, $publicKey);
    }

    /**
     * Create JWK to sign JWS with RSASSA-PKCS1-v1_5 using SHA-512.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @return Jwk
     */
    public function createSignRs512(string $privateKey, ?string $passPhrase): Jwk
    {
        return $this->createSignRsa(512, $privateKey, $passPhrase);
    }

    /**
     * Create JWK to verify JWS signed with RSASSA-PKCS1-v1_5 using SHA-512.
     *
     * @param string $publicKey
     * @return Jwk
     */
    public function createVerifyRs512(string $publicKey): Jwk
    {
        return $this->createVerifyRsa(512, $publicKey);
    }

    /**
     * Create JWK to sign JWS with ECDSA using P-256 and SHA-256.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @return Jwk
     */
    public function createSignEs256(string $privateKey, ?string $passPhrase): Jwk
    {
        return $this->createSignEs(256, $privateKey, $passPhrase);
    }

    /**
     * Create JWK to verify JWS signed with ECDSA using P-256 and SHA-256.
     *
     * @param string $publicKey
     * @return Jwk
     */
    public function createVerifyEs256(string $publicKey): Jwk
    {
        return $this->createVerifyEs(256, $publicKey);
    }

    /**
     * Create JWK to sign JWS with ECDSA using P-384 and SHA-384 .
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @return Jwk
     */
    public function createSignEs384(string $privateKey, ?string $passPhrase): Jwk
    {
        return $this->createSignEs(384, $privateKey, $passPhrase);
    }

    /**
     * Create JWK to verify JWS signed with ECDSA using P-384 and SHA-384 .
     *
     * @param string $publicKey
     * @return Jwk
     */
    public function createVerifyEs384(string $publicKey): Jwk
    {
        return $this->createVerifyEs(384, $publicKey);
    }

    /**
     * Create JWK to sign JWS with ECDSA using P-521 and SHA-512.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @return Jwk
     */
    public function createSignEs512(string $privateKey, ?string $passPhrase): Jwk
    {
        return $this->createSignEs(512, $privateKey, $passPhrase);
    }

    /**
     * Create JWK to verify JWS signed with ECDSA using P-521 and SHA-512.
     *
     * @param string $publicKey
     * @return Jwk
     */
    public function createVerifyEs512(string $publicKey): Jwk
    {
        return $this->createVerifyEs(512, $publicKey);
    }

    /**
     * Create JWK to sign JWS with RSASSA-PSS using SHA-256 and MGF1 with SHA-256.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @return Jwk
     */
    public function createSignPs256(string $privateKey, ?string $passPhrase): Jwk
    {
        return $this->createSignPs(256, $privateKey, $passPhrase);
    }

    /**
     * Create JWK to verify JWS signed with RSASSA-PSS using SHA-256 and MGF1 with SHA-256.
     *
     * @param string $publicKey
     * @return Jwk
     */
    public function createVerifyPs256(string $publicKey): Jwk
    {
        return $this->createVerifyPs(256, $publicKey);
    }

    /**
     * Create JWK to sign JWS with RSASSA-PSS using SHA-384 and MGF1 with SHA-384.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @return Jwk
     */
    public function createSignPs384(string $privateKey, ?string $passPhrase): Jwk
    {
        return $this->createSignPs(384, $privateKey, $passPhrase);
    }

    /**
     * Create JWK to verify JWS signed with RSASSA-PSS using SHA-384 and MGF1 with SHA-384.
     *
     * @param string $publicKey
     * @return Jwk
     */
    public function createVerifyPs384(string $publicKey): Jwk
    {
        return $this->createVerifyPs(384, $publicKey);
    }

    /**
     * Create JWK to sign JWS with RSASSA-PSS using SHA-512 and MGF1 with SHA-512.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @return Jwk
     */
    public function createSignPs512(string $privateKey, ?string $passPhrase): Jwk
    {
        return $this->createSignPs(512, $privateKey, $passPhrase);
    }

    /**
     * Create JWK to verify JWS signed with RSASSA-PSS using SHA-512 and MGF1 with SHA-512.
     *
     * @param string $publicKey
     * @return Jwk
     */
    public function createVerifyPs512(string $publicKey): Jwk
    {
        return $this->createVerifyPs(512, $publicKey);
    }

    /**
     * Create key to use with A128KW algorithm to encrypt JWE.
     *
     * @param string $key
     * @return Jwk
     */
    public function createA128KW(string $key): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_A128KW);
    }

    private function createOct(string $key, string $use, string $algo): Jwk
    {
        if (strlen($key) < 128) {
            throw new \InvalidArgumentException('Shared secret key must be at least 128 bits.');
        }

        return new Jwk(
            Jwk::KEY_TYPE_OCTET,
            ['k' => self::base64Encode($key)],
            $use,
            null,
            $algo
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

    private function createSignPs(int $bits, string $key, ?string $pass): Jwk
    {
        $data = $this->createSignRsa($bits, $key, $pass)->getJsonData();
        $data['alg'] = 'PS' .$bits;

        return $this->createFromData($data);
    }

    private function createVerifyPs(int $bits, string $key): Jwk
    {
        $data = $this->createVerifyRsa($bits, $key)->getJsonData();
        $data['alg'] = 'PS' .$bits;

        return $this->createFromData($data);
    }

    private function createSignEs(int $bits, string $key, ?string $pass): Jwk
    {
        $resource = openssl_get_privatekey($key, (string)$pass);
        $keyData = openssl_pkey_get_details($resource)['ec'];
        openssl_free_key($resource);
        if (!array_key_exists($keyData['curve_oid'], self::EC_CURVE_MAP)) {
            throw new \RuntimeException('Unsupported EC curve');
        }
        if ($bits !== self::EC_CURVE_MAP[$keyData['curve_oid']]['bits']) {
            throw new \RuntimeException('The key cannot be used with SHA-' .$bits .' hashing algorithm');
        }

        return new Jwk(
            Jwk::KEY_TYPE_EC,
            [
                'd' => self::base64Encode($keyData['d']),
                'x' => self::base64Encode($keyData['x']),
                'y' => self::base64Encode($keyData['y']),
                'crv' => self::EC_CURVE_MAP[$keyData['curve_oid']]['name']
            ],
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            null,
            'ES' .$bits
        );
    }

    private function createVerifyEs(int $bits, string $key): Jwk
    {
        $resource = openssl_get_publickey($key);
        $keyData = openssl_pkey_get_details($resource)['ec'];
        openssl_free_key($resource);
        if (!array_key_exists($keyData['curve_oid'], self::EC_CURVE_MAP)) {
            throw new \RuntimeException('Unsupported EC curve');
        }
        if ($bits !== self::EC_CURVE_MAP[$keyData['curve_oid']]['bits']) {
            throw new \RuntimeException('The key cannot be used with SHA-' .$bits .' hashing algorithm');
        }

        return new Jwk(
            Jwk::KEY_TYPE_EC,
            [
                'x' => self::base64Encode($keyData['x']),
                'y' => self::base64Encode($keyData['y']),
                'crv' => self::EC_CURVE_MAP[$keyData['curve_oid']]['name']
            ],
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            null,
            'ES' .$bits
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
