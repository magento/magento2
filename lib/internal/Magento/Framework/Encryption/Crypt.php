<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Encryption;

/**
 * Class encapsulates cryptographic algorithm
 *
 * @api
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
     * Encryption algorithm module handle
     *
     * @var resource
     */
    protected $_handle;

    /**
     * Constructor
     *
     * @param  string      $key        Secret encryption key.
     *                                 It's unsafe to store encryption key in memory, so no getter for key exists.
     * @param  string      $cipher     Cipher algorithm (one of the MCRYPT_ciphername constants)
     * @param  string      $mode       Mode of cipher algorithm (MCRYPT_MODE_modeabbr constants)
     * @param  string|bool $initVector Initial vector to fill algorithm blocks.
     *                                 TRUE generates a random initial vector.
     *                                 FALSE fills initial vector with zero bytes to not use it.
     * @throws \Exception
     */
    public function __construct(
        $key,
        $cipher = MCRYPT_BLOWFISH,
        $mode = MCRYPT_MODE_ECB,
        $initVector = false
    ) {
        $this->_cipher = $cipher;
        $this->_mode = $mode;
        // @codingStandardsIgnoreStart
        $this->_handle = @mcrypt_module_open($cipher, '', $mode, '');
        // @codingStandardsIgnoreEnd
        try {
            // @codingStandardsIgnoreStart
            $maxKeySize = @mcrypt_enc_get_key_size($this->_handle);
            // @codingStandardsIgnoreEnd
            if (strlen($key) > $maxKeySize) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase('Key must not exceed %1 bytes.', [$maxKeySize])
                );
            }
            // @codingStandardsIgnoreStart
            $initVectorSize = @mcrypt_enc_get_iv_size($this->_handle);
            // @codingStandardsIgnoreEnd
            if (true === $initVector) {
                /* Generate a random vector from human-readable characters */
                $abc = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $initVector = '';
                for ($i = 0; $i < $initVectorSize; $i++) {
                    $initVector .= $abc[random_int(0, strlen($abc) - 1)];
                }
            } elseif (false === $initVector) {
                /* Set vector to zero bytes to not use it */
                $initVector = str_repeat("\0", $initVectorSize);
            } elseif (!is_string($initVector) || strlen($initVector) != $initVectorSize) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase(
                        'Init vector must be a string of %1 bytes.',
                        [$initVectorSize]
                    )
                );
            }
            $this->_initVector = $initVector;
        } catch (\Exception $e) {
            // @codingStandardsIgnoreStart
            @mcrypt_module_close($this->_handle);
            // @codingStandardsIgnoreEnd
            throw $e;
        }
        // @codingStandardsIgnoreStart
        @mcrypt_generic_init($this->_handle, $key, $initVector);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Destructor frees allocated resources
     */
    public function __destruct()
    {
        // @codingStandardsIgnoreStart
        @mcrypt_generic_deinit($this->_handle);
        // @codingStandardsIgnoreEnd
        // @codingStandardsIgnoreStart
        @mcrypt_module_close($this->_handle);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Retrieve a name of currently used cryptographic algorithm
     *
     * @return string
     */
    public function getCipher()
    {
        return $this->_cipher;
    }

    /**
     * Mode in which cryptographic algorithm is running
     *
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * Retrieve an actual value of initial vector that has been used to initialize a cipher
     *
     * @return string
     */
    public function getInitVector()
    {
        return $this->_initVector;
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
        // @codingStandardsIgnoreStart
        return @mcrypt_generic($this->_handle, $data);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Decrypt a data
     *
     * @param  string $data String to decrypt
     * @return string
     */
    public function decrypt($data)
    {
        if (strlen($data) == 0) {
            return $data;
        }
        // @codingStandardsIgnoreStart
        $data = @mdecrypt_generic($this->_handle, $data);
        // @codingStandardsIgnoreEnd
        /*
         * Returned string can in fact be longer than the unencrypted string due to the padding of the data
         * @link http://www.php.net/manual/en/function.mdecrypt-generic.php
         */
        $data = rtrim($data, "\0");
        return $data;
    }
}
