<?php

namespace Magento\Framework\Encryption\Adapter;

use Magento\Framework\Encryption\Encryptor;

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
     * @param $data
     * @return string
     */
    public function encrypt($data)
    {
        $cipherText = sodium_crypto_aead_chacha20poly1305_encrypt(
            (string)$data,
            '',
            random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES),
            $this->key
        );

        return $this->keyVersion . ':' . Encryptor::CIPHER_AEAD_CHACHA20POLY1305 . ':' . base64_encode($cipherText);
    }

    /**
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        $nonce = mb_substr($data, 0, SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES, '8bit');
        $payload = mb_substr($data, SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES, null, '8bit');
        
        return sodium_crypto_aead_chacha20poly1305_decrypt(
            $payload,
            '',
            $nonce,
            $this->key
        );
    }
}
