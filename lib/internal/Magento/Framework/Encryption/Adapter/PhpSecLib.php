<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Encryption\Adapter;

use phpseclib3\Crypt\Blowfish;
use phpseclib3\Crypt\Rijndael;

/**
 * PhpSecLib adapter for decrypting values using legacy ciphers
 */
class PhpSecLib implements EncryptionAdapterInterface
{
    /**
     * @var \phpseclib3\Crypt\Common\SymmetricKey
     */
    private $cipher;

    /**
     * PhpSecLib constructor.
     *
     * @param string $key
     * @param string $cipher
     * @param string $mode
     * @param string|null $initVector
     * @throws \Exception
     */
    public function __construct(
        string $key,
        string $cipher = MCRYPT_BLOWFISH,
        string $mode = MCRYPT_MODE_ECB,
        ?string $initVector = null
    ) {
        $mode = strtolower(str_replace('mcrypt_mode_', '', $mode));

        switch ($cipher) {
            case MCRYPT_RIJNDAEL_128:
                $this->cipher = new Rijndael($mode);
                $this->cipher->setBlockLength(128);
                break;
            case MCRYPT_RIJNDAEL_256:
                $this->cipher = new Rijndael($mode);
                $this->cipher->setBlockLength(256);
                break;
            case MCRYPT_BLOWFISH:
            default:
                $this->cipher = new Blowfish($mode);
                break;
        }

        $this->cipher->setKey($key);
        if ($initVector) {
            $this->cipher->setIV($initVector);
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
        if (strlen($data) == 0) {
            return $data;
        }
        return $this->cipher->encrypt($data);
    }

    /**
     * Decrypt a string
     *
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        if (strlen($data) == 0) {
            return $data;
        }
        return $this->cipher->decrypt($data);
    }
}
