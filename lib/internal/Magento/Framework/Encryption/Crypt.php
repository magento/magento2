<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Encryption;

/**
 * Class encapsulates cryptographic algorithm
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
    public function __construct($key, $cipher = MCRYPT_BLOWFISH, $mode = MCRYPT_MODE_ECB, $initVector = false)
    {
        $this->_cipher = $cipher;
        $this->_mode = $mode;
        $this->_handle = mcrypt_module_open($cipher, '', $mode, '');
        try {
            $maxKeySize = mcrypt_enc_get_key_size($this->_handle);
            if (strlen($key) > $maxKeySize) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase('Key must not exceed %1 bytes.', [$maxKeySize])
                );
            }
            $initVectorSize = mcrypt_enc_get_iv_size($this->_handle);
            if (true === $initVector) {
                /* Generate a random vector from human-readable characters */
                $abc = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $initVector = '';
                for ($i = 0; $i < $initVectorSize; $i++) {
                    $initVector .= $abc[rand(0, strlen($abc) - 1)];
                }
            } elseif (false === $initVector) {
                /* Set vector to zero bytes to not use it */
                $initVector = str_repeat("\0", $initVectorSize);
            } elseif (!is_string($initVector) || strlen($initVector) != $initVectorSize) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase('Init vector must be a string of %1 bytes.', [$initVectorSize])
                );
            }
            $this->_initVector = $initVector;
        } catch (\Exception $e) {
            mcrypt_module_close($this->_handle);
            throw $e;
        }
        mcrypt_generic_init($this->_handle, $key, $initVector);
    }

    /**
     * Destructor frees allocated resources
     *
     * @return void
     */
    public function __destruct()
    {
        mcrypt_generic_deinit($this->_handle);
        mcrypt_module_close($this->_handle);
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
        return mcrypt_generic($this->_handle, $data);
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
        $data = mdecrypt_generic($this->_handle, $data);
        /*
         * Returned string can in fact be longer than the unencrypted string due to the padding of the data
         * @link http://www.php.net/manual/en/function.mdecrypt-generic.php
         */
        $data = rtrim($data, "\0");
        return $data;
    }
}
