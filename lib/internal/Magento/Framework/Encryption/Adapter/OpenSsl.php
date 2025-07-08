<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Encryption\Adapter;

use Magento\Framework\Encryption\Encryptor;

/**
 * OpenSSL adapter for encrypting/decrypting values
 */
class OpenSsl implements EncryptionAdapterInterface
{
    private string $key;
    private string $cipherMethod;
    private ?string $initVector;
    private int $mode;

    /**
     * OpenSsl constructor.
     *
     * @param string $key
     * @param int $cipher
     * @param int $mode
     * @param string|null $initVector
     * @throws \Exception
     */
    public function __construct(
        string $key,
        int $cipher,
        int $mode,
        ?string $initVector = null
    ) {
        $this->key = $key;
        $this->mode = $mode;
        $this->initVector = $initVector;

        switch ($cipher) {
            case Encryptor::CIPHER_RIJNDAEL_128:
                $this->cipherMethod = 'aes-128-ecb';
                break;
            case Encryptor::CIPHER_RIJNDAEL_256:
                $this->cipherMethod = 'aes-256-cbc';
                break;
            case Encryptor::CIPHER_BLOWFISH:
            default:
                $this->cipherMethod = 'bf-ecb';
                break;
        }

        // For CBC mode, if IV is not provided for encryption, generate one.
        // For decryption, the IV is expected to be part of the data or provided.
        if ($this->mode === Encryptor::MODE_CBC && $this->initVector === null) {
            $this->initVector = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipherMethod));
        }
    }

    /**
     * Encrypt a string
     *
     * @param  string $data String to encrypt
     * @return string
     */
    public function encrypt(string $data): string
    {
        if (strlen($data) === 0) {
            return $data;
        }

        $encrypted = openssl_encrypt(
            $data,
            $this->cipherMethod,
            $this->key,
            OPENSSL_RAW_DATA,
            $this->initVector
        );

        if ($encrypted === false) {
            throw new \RuntimeException('OpenSSL encryption failed.');
        }

        // Prepend IV if CBC mode is used and IV was generated internally
        if ($this->mode === Encryptor::MODE_CBC && $this->initVector !== null) {
            return $this->initVector . $encrypted;
        }

        return $encrypted;
    }

    /**
     * Decrypt a string
     *
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        if (strlen($data) === 0) {
            return $data;
        }

        $iv = null;
        $encryptedData = $data;

        // If the IV was prepended during encryption, extract it.
        // This logic is primarily for decryption of data encrypted by this adapter.
        // For legacy data, the Encryptor::decrypt method handles IV extraction.
        if ($this->mode === Encryptor::MODE_CBC && $this->initVector !== null) {
            $ivLength = openssl_cipher_iv_length($this->cipherMethod);
            if (strlen($data) < $ivLength) {
                throw new \RuntimeException('Invalid data for decryption: IV missing or too short.');
            }
            $iv = substr($data, 0, $ivLength);
            $encryptedData = substr($data, $ivLength);
        }

        $decrypted = openssl_decrypt(
            $encryptedData,
            $this->cipherMethod,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv ?? $this->initVector // Use extracted IV or stored IV
        );

        if ($decrypted === false) {
            throw new \RuntimeException('OpenSSL decryption failed.');
        }

        return $decrypted;
    }
}
