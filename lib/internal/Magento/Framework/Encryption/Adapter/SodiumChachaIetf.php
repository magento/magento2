<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Encryption\Adapter;

/**
 * Sodium adapter for encrypting and decrypting strings
 */
class SodiumChachaIetf implements EncryptionAdapterInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * Sodium constructor.
     * @param string $key
     */
    public function __construct(
        string $key
    ) {
        $this->key = $key;
    }

    /**
     * Encrypt a string
     *
     * @param string $data
     * @return string string
     * @throws \SodiumException
     */
    public function encrypt(string $data): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES);
        $cipherText = sodium_crypto_aead_chacha20poly1305_ietf_encrypt(
            (string)$data,
            $nonce,
            $nonce,
            $this->key
        );

        return $nonce . $cipherText;
    }

    /**
     * Decrypt a string
     *
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        $nonce = mb_substr($data, 0, SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES, '8bit');
        $payload = mb_substr($data, SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES, null, '8bit');

        try {
            $plainText = sodium_crypto_aead_chacha20poly1305_ietf_decrypt(
                $payload,
                $nonce,
                $nonce,
                $this->key
            );
        } catch (\SodiumException $e) {
            $plainText = '';
        }

        return $plainText !== false ? $plainText : '';
    }
}
