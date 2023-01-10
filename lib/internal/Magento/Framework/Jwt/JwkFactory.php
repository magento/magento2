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
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createHs256(string $key, ?string $kid = null): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_HS256, $kid);
    }

    /**
     * Create JWK for signatures generated with HMAC and SHA384
     *
     * @param string $key
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createHs384(string $key, ?string $kid = null): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_HS384, $kid);
    }

    /**
     * Create JWK for signatures generated with HMAC and SHA512
     *
     * @param string $key
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createHs512(string $key, ?string $kid = null): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_HS512, $kid);
    }

    /**
     * Create JWK to sign JWS with RSASSA-PKCS1-v1_5 using SHA-256.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createSignRs256(string $privateKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateRsa(
            $privateKey,
            $passPhrase,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            Jwk::ALGORITHM_RS256,
            $kid
        );
    }

    /**
     * Create JWK to verify JWS signed with RSASSA-PKCS1-v1_5 using SHA-256.
     *
     * @param string $publicKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createVerifyRs256(string $publicKey, ?string $kid = null): Jwk
    {
        return $this->createPublicRsa($publicKey, Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_RS256, $kid);
    }

    /**
     * Create JWK to sign JWS with RSASSA-PKCS1-v1_5 using SHA-384.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createSignRs384(string $privateKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateRsa(
            $privateKey,
            $passPhrase,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            Jwk::ALGORITHM_RS384,
            $kid
        );
    }

    /**
     * Create JWK to verify JWS signed with RSASSA-PKCS1-v1_5 using SHA-384.
     *
     * @param string $publicKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createVerifyRs384(string $publicKey, ?string $kid = null): Jwk
    {
        return $this->createPublicRsa($publicKey, Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_RS384, $kid);
    }

    /**
     * Create JWK to sign JWS with RSASSA-PKCS1-v1_5 using SHA-512.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createSignRs512(string $privateKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateRsa(
            $privateKey,
            $passPhrase,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            Jwk::ALGORITHM_RS512,
            $kid
        );
    }

    /**
     * Create JWK to verify JWS signed with RSASSA-PKCS1-v1_5 using SHA-512.
     *
     * @param string $publicKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createVerifyRs512(string $publicKey, ?string $kid = null): Jwk
    {
        return $this->createPublicRsa($publicKey, Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_RS512, $kid);
    }

    /**
     * Create JWK to sign JWS with ECDSA using P-256 and SHA-256.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createSignEs256(string $privateKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateEc(
            $privateKey,
            $passPhrase,
            256,
            Jwk::ALGORITHM_ES256,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            $kid
        );
    }

    /**
     * Create JWK to verify JWS signed with ECDSA using P-256 and SHA-256.
     *
     * @param string $publicKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createVerifyEs256(string $publicKey, ?string $kid = null): Jwk
    {
        return $this->createPublicEc(
            $publicKey,
            256,
            Jwk::ALGORITHM_ES256,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            $kid
        );
    }

    /**
     * Create JWK to sign JWS with ECDSA using P-384 and SHA-384 .
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createSignEs384(string $privateKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateEc(
            $privateKey,
            $passPhrase,
            384,
            Jwk::ALGORITHM_ES384,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            $kid
        );
    }

    /**
     * Create JWK to verify JWS signed with ECDSA using P-384 and SHA-384 .
     *
     * @param string $publicKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createVerifyEs384(string $publicKey, ?string $kid = null): Jwk
    {
        return $this->createPublicEc(
            $publicKey,
            384,
            Jwk::ALGORITHM_ES384,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            $kid
        );
    }

    /**
     * Create JWK to sign JWS with ECDSA using P-521 and SHA-512.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createSignEs512(string $privateKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateEc(
            $privateKey,
            $passPhrase,
            512,
            Jwk::ALGORITHM_ES512,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            $kid
        );
    }

    /**
     * Create JWK to verify JWS signed with ECDSA using P-521 and SHA-512.
     *
     * @param string $publicKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createVerifyEs512(string $publicKey, ?string $kid = null): Jwk
    {
        return $this->createPublicEc(
            $publicKey,
            512,
            Jwk::ALGORITHM_ES512,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            $kid
        );
    }

    /**
     * Create JWK to sign JWS with RSASSA-PSS using SHA-256 and MGF1 with SHA-256.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createSignPs256(string $privateKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateRsa(
            $privateKey,
            $passPhrase,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            Jwk::ALGORITHM_PS256,
            $kid
        );
    }

    /**
     * Create JWK to verify JWS signed with RSASSA-PSS using SHA-256 and MGF1 with SHA-256.
     *
     * @param string $publicKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createVerifyPs256(string $publicKey, ?string $kid = null): Jwk
    {
        return $this->createPublicRsa($publicKey, Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_PS256, $kid);
    }

    /**
     * Create JWK to sign JWS with RSASSA-PSS using SHA-384 and MGF1 with SHA-384.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createSignPs384(string $privateKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateRsa(
            $privateKey,
            $passPhrase,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            Jwk::ALGORITHM_PS384,
            $kid
        );
    }

    /**
     * Create JWK to verify JWS signed with RSASSA-PSS using SHA-384 and MGF1 with SHA-384.
     *
     * @param string $publicKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createVerifyPs384(string $publicKey, ?string $kid = null): Jwk
    {
        return $this->createPublicRsa($publicKey, Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_PS384, $kid);
    }

    /**
     * Create JWK to sign JWS with RSASSA-PSS using SHA-512 and MGF1 with SHA-512.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createSignPs512(string $privateKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateRsa(
            $privateKey,
            $passPhrase,
            Jwk::PUBLIC_KEY_USE_SIGNATURE,
            Jwk::ALGORITHM_PS512,
            $kid
        );
    }

    /**
     * Create JWK to verify JWS signed with RSASSA-PSS using SHA-512 and MGF1 with SHA-512.
     *
     * @param string $publicKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createVerifyPs512(string $publicKey, ?string $kid = null): Jwk
    {
        return $this->createPublicRsa($publicKey, Jwk::PUBLIC_KEY_USE_SIGNATURE, Jwk::ALGORITHM_PS512, $kid);
    }

    /**
     * Create key to use with A128KW algorithm to encrypt JWE.
     *
     * @param string $key
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createA128KW(string $key, ?string $kid = null): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_A128KW, $kid);
    }

    /**
     * Create key to use with A192KW algorithm to encrypt JWE.
     *
     * @param string $key
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createA192KW(string $key, ?string $kid = null): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_A192KW, $kid);
    }

    /**
     * Create key to use with A256KW algorithm to encrypt JWE.
     *
     * @param string $key
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createA256KW(string $key, ?string $kid = null): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_A256KW, $kid);
    }

    /**
     * Create RSA key to use with RSA-OAEP algorithm to encrypt JWE.
     *
     * @param string $publicKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createEncryptRsaOaep(string $publicKey, ?string $kid = null): Jwk
    {
        return $this->createPublicRsa($publicKey, Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_RSA_OAEP, $kid);
    }

    /**
     * Create RSA key to use with RSA-OAEP algorithm to decrypt JWE.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createDecryptRsaOaep(string $privateKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateRsa(
            $privateKey,
            $passPhrase,
            Jwk::PUBLIC_KEY_USE_ENCRYPTION,
            Jwk::ALGORITHM_RSA_OAEP,
            $kid
        );
    }

    /**
     * Create RSA key to use with RSA-OAEP-256 algorithm to encrypt JWE.
     *
     * @param string $publicKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createEncryptRsaOaep256(string $publicKey, ?string $kid = null): Jwk
    {
        return $this->createPublicRsa($publicKey, Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_RSA_OAEP_256, $kid);
    }

    /**
     * Create RSA key to use with RSA-OAEP-256 algorithm to decrypt JWE.
     *
     * @param string $privateKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createDecryptRsaOaep256(string $privateKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateRsa(
            $privateKey,
            $passPhrase,
            Jwk::PUBLIC_KEY_USE_ENCRYPTION,
            Jwk::ALGORITHM_RSA_OAEP_256,
            $kid
        );
    }

    /**
     * Create JWK to use with "dir" algorithm for JWEs.
     *
     * @param string $key
     * @param string $contentEncryptionAlgorithm
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createDir(string $key, string $contentEncryptionAlgorithm, ?string $kid = null): Jwk
    {
        if (strlen($key) < 2048) {
            throw new \InvalidArgumentException('Shared secret key must be at least 2048 bits.');
        }

        return new Jwk(
            Jwk::KEY_TYPE_OCTET,
            ['k' => self::base64Encode($key)],
            Jwk::PUBLIC_KEY_USE_ENCRYPTION,
            null,
            Jwk::ALGORITHM_DIR,
            null,
            null,
            null,
            null,
            $kid,
            $contentEncryptionAlgorithm
        );
    }

    /**
     * Create JWK to use ECDH-ES algorithm with EC key to encrypt JWE.
     *
     * @param string $publicEcKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createEncryptEcdhEsWithEc(string $publicEcKey, ?string $kid = null): Jwk
    {
        return $this->createPublicEc(
            $publicEcKey,
            null,
            Jwk::ALGORITHM_ECDH_ES,
            Jwk::PUBLIC_KEY_USE_ENCRYPTION,
            $kid
        );
    }

    /**
     * Create JWK to use ECDH-ES algorithm with EC key to decrypt JWE.
     *
     * @param string $privateEcKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createDecryptEcdhEsWithEc(string $privateEcKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateEc(
            $privateEcKey,
            $passPhrase,
            null,
            Jwk::ALGORITHM_ECDH_ES,
            Jwk::PUBLIC_KEY_USE_ENCRYPTION,
            $kid
        );
    }

    /**
     * Create JWK to use ECDH-ES+A128KW algorithm with EC key to encrypt JWE.
     *
     * @param string $publicEcKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createEncryptEcdhEsA128kwWithEc(string $publicEcKey, ?string $kid = null): Jwk
    {
        return $this->createPublicEc(
            $publicEcKey,
            null,
            Jwk::ALGORITHM_ECDH_ES_A128KW,
            Jwk::PUBLIC_KEY_USE_ENCRYPTION,
            $kid
        );
    }

    /**
     * Create JWK to use ECDH-ES+A128KW algorithm with EC key to decrypt JWE.
     *
     * @param string $privateEcKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createDecryptEcdhEsA128kwWithEc(string $privateEcKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateEc(
            $privateEcKey,
            $passPhrase,
            null,
            Jwk::ALGORITHM_ECDH_ES_A128KW,
            Jwk::PUBLIC_KEY_USE_ENCRYPTION,
            $kid
        );
    }

    /**
     * Create JWK to use ECDH-ES+A192KW algorithm with EC key to encrypt JWE.
     *
     * @param string $publicEcKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createEncryptEcdhEsA192kwWithEc(string $publicEcKey, ?string $kid = null): Jwk
    {
        return $this->createPublicEc(
            $publicEcKey,
            null,
            Jwk::ALGORITHM_ECDH_ES_A192KW,
            Jwk::PUBLIC_KEY_USE_ENCRYPTION,
            $kid
        );
    }

    /**
     * Create JWK to use ECDH-ES+A192KW algorithm with EC key to decrypt JWE.
     *
     * @param string $privateEcKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createDecryptEcdhEsA192kwWithEc(string $privateEcKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateEc(
            $privateEcKey,
            $passPhrase,
            null,
            Jwk::ALGORITHM_ECDH_ES_A192KW,
            Jwk::PUBLIC_KEY_USE_ENCRYPTION,
            $kid
        );
    }

    /**
     * Create JWK to use ECDH-ES+A256KW algorithm with EC key to encrypt JWE.
     *
     * @param string $publicEcKey
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createEncryptEcdhEsA256kwWithEc(string $publicEcKey, ?string $kid = null): Jwk
    {
        return $this->createPublicEc(
            $publicEcKey,
            null,
            Jwk::ALGORITHM_ECDH_ES_A256KW,
            Jwk::PUBLIC_KEY_USE_ENCRYPTION,
            $kid
        );
    }

    /**
     * Create JWK to use ECDH-ES+A256KW algorithm with EC key to decrypt JWE.
     *
     * @param string $privateEcKey
     * @param string|null $passPhrase
     * @param string|null $kid JWK ID.
     * @return Jwk
     */
    public function createDecryptEcdhEsA256kwWithEc(string $privateEcKey, ?string $passPhrase, ?string $kid = null): Jwk
    {
        return $this->createPrivateEc(
            $privateEcKey,
            $passPhrase,
            null,
            Jwk::ALGORITHM_ECDH_ES_A256KW,
            Jwk::PUBLIC_KEY_USE_ENCRYPTION,
            $kid
        );
    }

    /**
     * Create JWK to encrypt/decrypt JWEs with A128GCMKW algorithm.
     *
     * @param string $key
     * @param string|null $kid
     * @return Jwk
     */
    public function createA128Gcmkw(string $key, ?string $kid = null): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_A128GCMKW, $kid);
    }

    /**
     * Create JWK to encrypt/decrypt JWEs with A192GCMKW algorithm.
     *
     * @param string $key
     * @param string|null $kid
     * @return Jwk
     */
    public function createA192Gcmkw(string $key, ?string $kid = null): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_A192GCMKW, $kid);
    }

    /**
     * Create JWK to encrypt/decrypt JWEs with A256GCMKW algorithm.
     *
     * @param string $key
     * @param string|null $kid
     * @return Jwk
     */
    public function createA256Gcmkw(string $key, ?string $kid = null): Jwk
    {
        return $this->createOct($key, Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_A256GCMKW, $kid);
    }

    /**
     * Create JWK to encrypt/decrypt JWEs with PBES2-HS256+A128KW algorithm.
     *
     * @param string $password
     * @param string|null $kid
     * @return Jwk
     */
    public function createPbes2Hs256A128kw(string $password, ?string $kid = null): Jwk
    {
        return $this->createOct($password, Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_PBES2_HS256_A128KW, $kid);
    }

    /**
     * Create JWK to encrypt/decrypt JWEs with PBES2-HS384+A192KW algorithm.
     *
     * @param string $password
     * @param string|null $kid
     * @return Jwk
     */
    public function createPbes2Hs384A192kw(string $password, ?string $kid = null): Jwk
    {
        return $this->createOct($password, Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_PBES2_HS384_A192KW, $kid);
    }

    /**
     * Create JWK to encrypt/decrypt JWEs with PBES2-HS512+A256KW algorithm.
     *
     * @param string $password
     * @param string|null $kid
     * @return Jwk
     */
    public function createPbes2Hs512A256kw(string $password, ?string $kid = null): Jwk
    {
        return $this->createOct($password, Jwk::PUBLIC_KEY_USE_ENCRYPTION, Jwk::ALGORITHM_PBES2_HS512_A256KW, $kid);
    }

    public function createNone(): Jwk
    {
        return new Jwk(
            'none',
            [],
            null,
            [Jwk::KEY_OP_SIGN, Jwk::KEY_OP_ENCRYPT],
            'none'
        );
    }

    private function createOct(string $key, string $use, string $algo, ?string $kid): Jwk
    {
        if (strlen($key) < 2048) {
            throw new \InvalidArgumentException('Shared secret key must be at least 2048 bits.');
        }

        return new Jwk(
            Jwk::KEY_TYPE_OCTET,
            ['k' => self::base64Encode($key)],
            $use,
            null,
            $algo,
            null,
            null,
            null,
            null,
            $kid
        );
    }

    private function createPrivateRsa(string $key, ?string $pass, string $use, string $algorithm, ?string $kid): Jwk
    {
        $resource = openssl_get_privatekey($key, (string)$pass);
        $keyData = openssl_pkey_get_details($resource)['rsa'];
        $this->freeResource($resource);
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
            $use,
            null,
            $algorithm,
            null,
            null,
            null,
            null,
            $kid
        );
    }

    private function createPublicRsa(string $key, string $use, string $algorithm, ?string $kid): Jwk
    {
        $resource = openssl_get_publickey($key);
        $keyData = openssl_pkey_get_details($resource)['rsa'];
        $this->freeResource($resource);
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
            $use,
            null,
            $algorithm,
            null,
            null,
            null,
            null,
            $kid
        );
    }

    private function createPrivateEc(
        string $key,
        ?string $pass,
        ?int $validateCurveBits,
        string $algorithm,
        string $use,
        ?string $kid
    ): Jwk {
        $resource = openssl_get_privatekey($key, (string)$pass);
        $keyData = openssl_pkey_get_details($resource)['ec'];
        $this->freeResource($resource);
        if (!array_key_exists($keyData['curve_oid'], self::EC_CURVE_MAP)) {
            throw new \RuntimeException('Unsupported EC curve');
        }
        if ($validateCurveBits && $validateCurveBits !== self::EC_CURVE_MAP[$keyData['curve_oid']]['bits']) {
            throw new \RuntimeException(
                'The key cannot be used with SHA-' .$validateCurveBits .' hashing algorithm'
            );
        }

        return new Jwk(
            Jwk::KEY_TYPE_EC,
            [
                'd' => self::base64Encode($keyData['d']),
                'x' => self::base64Encode($keyData['x']),
                'y' => self::base64Encode($keyData['y']),
                'crv' => self::EC_CURVE_MAP[$keyData['curve_oid']]['name']
            ],
            $use,
            null,
            $algorithm,
            null,
            null,
            null,
            null,
            $kid
        );
    }

    private function createPublicEc(
        string $key,
        ?int $validateCurveBits,
        string $algorithm,
        string $use,
        ?string $kid
    ): Jwk {
        $resource = openssl_get_publickey($key);
        $keyData = openssl_pkey_get_details($resource)['ec'];
        $this->freeResource($resource);
        if (!array_key_exists($keyData['curve_oid'], self::EC_CURVE_MAP)) {
            throw new \RuntimeException('Unsupported EC curve');
        }
        if ($validateCurveBits && $validateCurveBits !== self::EC_CURVE_MAP[$keyData['curve_oid']]['bits']) {
            throw new \RuntimeException(
                'The key cannot be used with SHA-' .$validateCurveBits .' hashing algorithm'
            );
        }

        return new Jwk(
            Jwk::KEY_TYPE_EC,
            [
                'x' => self::base64Encode($keyData['x']),
                'y' => self::base64Encode($keyData['y']),
                'crv' => self::EC_CURVE_MAP[$keyData['curve_oid']]['name']
            ],
            $use,
            null,
            $algorithm,
            null,
            null,
            null,
            null,
            $kid
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

    /**
     * @param mixed $resource
     *
     * @return void
     */
    private function freeResource($resource): void
    {
        if (\is_resource($resource) && (version_compare(PHP_VERSION, '8.0') < 0)) {
            openssl_free_key($resource);
        }
    }
}
