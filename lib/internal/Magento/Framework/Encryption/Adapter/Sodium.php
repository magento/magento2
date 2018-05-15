<?php

declare(strict_types=1);

namespace Magento\Framework\Encryption\Adapter;

use Magento\Framework\Encryption\Encryptor;

/**
 * Sodium adapter for encrypting and decrypting strings
 */
class Sodium implements EncryptionAdapterInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var int
     */
    private $keyVersion;

    /**
     * Sodium constructor.
     * @param string $key
     * @param int|null $keyVersion
     */
    public function __construct(
        string $key,
        int $keyVersion = null
    ) {
        $this->key = $key;
        $this->keyVersion = $keyVersion;
    }

    /**
     * Encrypt a string
     *
     * @param string $data
     * @return string string
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

        return $this->keyVersion .
            ':' . Encryptor::CIPHER_AEAD_CHACHA20POLY1305 .
            ':' . base64_encode($nonce . $cipherText);
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

        $plainText = sodium_crypto_aead_chacha20poly1305_ietf_decrypt(
            $payload,
            $nonce,
            $nonce,
            $this->key
        );

        return $plainText;
    }
}
