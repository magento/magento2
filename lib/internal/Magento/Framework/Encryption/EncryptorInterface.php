<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Encryption;

/**
 * Encryptor interface
 *
 * @api
 * @since 2.0.0
 */
interface EncryptorInterface
{
    /**
     * Generate a [salted] hash.
     *
     * $salt can be:
     * false - salt is not used
     * true - random salt of the default length will be generated
     * integer - random salt of specified length will be generated
     * string - actual salt value to be used
     *
     * @param string $password
     * @param bool|int|string $salt
     * @return string
     * @since 2.0.0
     */
    public function getHash($password, $salt = false);

    /**
     * Hash a string
     *
     * @param string $data
     * @return string
     * @since 2.0.0
     */
    public function hash($data);

    /**
     * Validate hash against hashing method (with or without salt)
     *
     * @param string $password
     * @param string $hash
     * @return bool
     * @throws \Exception
     * @since 2.0.0
     */
    public function validateHash($password, $hash);

    /**
     * Validate hash against hashing method (with or without salt)
     *
     * @param string $password
     * @param string $hash
     * @return bool
     * @throws \Exception
     * @since 2.0.0
     */
    public function isValidHash($password, $hash);

    /**
     * Validate hashing algorithm version
     *
     * @param string $hash
     * @param bool $validateCount
     * @return bool
     * @since 2.0.0
     */
    public function validateHashVersion($hash, $validateCount = false);

    /**
     * Encrypt a string
     *
     * @param string $data
     * @return string
     * @since 2.0.0
     */
    public function encrypt($data);

    /**
     * Decrypt a string
     *
     * @param string $data
     * @return string
     * @since 2.0.0
     */
    public function decrypt($data);

    /**
     * Return crypt model, instantiate if it is empty
     *
     * @param string $key
     * @return \Magento\Framework\Encryption\Crypt
     * @since 2.0.0
     */
    public function validateKey($key);
}
