<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Encryption;

/**
 * Class encapsulates cryptographic algorithm
 *
 * @api
 * @deprecated
 */
class Crypt
{
    /**
     * @var string
     */
    protected $_cipher;

    /**
     * @var string
     */
    protected $_mode;

    /**
     * @var string
     */
    protected $_initVector;

    /**
     * Mcrypt adapter
     *
     * @var \Magento\Framework\Encryption\Adapter\Mcrypt
     */
    private $mcrypt;

    /**
     * Constructor
     *
     * @param string $key Secret encryption key.
     *                    It's unsafe to store encryption key in memory, so no getter for key exists.
     * @param string $cipher Cipher algorithm (one of the MCRYPT_ciphername constants)
     * @param string $mode Mode of cipher algorithm (MCRYPT_MODE_modeabbr constants)
     * @param string|bool $initVector Initial vector to fill algorithm blocks.
     *                                TRUE generates a random initial vector.
     *                                FALSE fills initial vector with zero bytes to not use it.
     * @throws \Exception
     */
    public function __construct(
        $key,
        $cipher = MCRYPT_BLOWFISH,
        $mode = MCRYPT_MODE_ECB,
        $initVector = false
    ) {
        if (true === $initVector) {
            // @codingStandardsIgnoreStart
            $handle = @mcrypt_module_open($cipher, '', $mode, '');
            $initVectorSize = @mcrypt_enc_get_iv_size($handle);
            // @codingStandardsIgnoreEnd

            /* Generate a random vector from human-readable characters */
            $allowedCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $initVector = '';
            for ($i = 0; $i < $initVectorSize; $i++) {
                $initVector .= $allowedCharacters[random_int(0, strlen($allowedCharacters) - 1)];
            }
            // @codingStandardsIgnoreStart
            @mcrypt_generic_deinit($handle);
            @mcrypt_module_close($handle);
            // @codingStandardsIgnoreEnd
        }

        $this->mcrypt = new \Magento\Framework\Encryption\Adapter\Mcrypt(
            $key,
            $cipher,
            $mode,
            $initVector === false ? null : $initVector
        );
    }

    /**
     * Retrieve a name of currently used cryptographic algorithm
     *
     * @return string
     */
    public function getCipher()
    {
        return $this->mcrypt->getCipher();
    }

    /**
     * Mode in which cryptographic algorithm is running
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mcrypt->getMode();
    }

    /**
     * Retrieve an actual value of initial vector that has been used to initialize a cipher
     *
     * @return string
     */
    public function getInitVector()
    {
        return $this->mcrypt->getInitVector();
    }

    /**
     * Encrypt a data
     *
     * @param  string $data String to encrypt
     * @return string
     */
    public function encrypt($data)
    {
        if (strlen($data) == 0) {
            return $data;
        }
        // @codingStandardsIgnoreLine
        return @mcrypt_generic($this->mcrypt->getHandle(), $data);
    }

    /**
     * Decrypt a data
     *
     * @param  string $data String to decrypt
     * @return string
     */
    public function decrypt($data)
    {
        return $this->mcrypt->decrypt($data);
    }
}
