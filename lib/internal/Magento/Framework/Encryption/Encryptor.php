<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Encryption;

use Magento\Framework\App\DeploymentConfig;

/**
 * Provides basic logic for hashing passwords and encrypting/decrypting misc data
 */
class Encryptor implements EncryptorInterface
{
    /**
     * Array key of encryption key in deployment config
     */
    const PARAM_CRYPT_KEY = 'crypt/key';

    /**#@+
     * Hash and Cipher versions
     */
    const HASH_VERSION_MD5 = 0;

    const HASH_VERSION_SHA256 = 1;

    const HASH_VERSION_LATEST = 1;

    const CIPHER_BLOWFISH = 0;

    const CIPHER_RIJNDAEL_128 = 1;

    const CIPHER_RIJNDAEL_256 = 2;

    const CIPHER_LATEST = 2;
    /**#@-*/

    /**
     * Default length of salt in bytes
     */
    const DEFAULT_SALT_LENGTH = 32;

    /**
     * Indicate cipher
     *
     * @var int
     */
    protected $cipher = self::CIPHER_LATEST;

    /**
     * Version of encryption key
     *
     * @var int
     */
    protected $keyVersion;

    /**
     * Array of encryption keys
     *
     * @var string[]
     */
    protected $keys = [];

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $randomGenerator;

    /**
     * @param \Magento\Framework\Math\Random $randomGenerator
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        \Magento\Framework\Math\Random $randomGenerator,
        DeploymentConfig $deploymentConfig
    ) {
        $this->randomGenerator = $randomGenerator;
        // load all possible keys
        $this->keys = preg_split('/\s+/s', trim($deploymentConfig->get(self::PARAM_CRYPT_KEY)));
        $this->keyVersion = count($this->keys) - 1;
    }

    /**
     * Check whether specified cipher version is supported
     *
     * Returns matched supported version or throws exception
     *
     * @param int $version
     * @return int
     * @throws \Exception
     */
    public function validateCipher($version)
    {
        $types = [self::CIPHER_BLOWFISH, self::CIPHER_RIJNDAEL_128, self::CIPHER_RIJNDAEL_256];

        $version = (int)$version;
        if (!in_array($version, $types, true)) {
            throw new \Exception(__('Not supported cipher version'));
        }
        return $version;
    }

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
     */
    public function getHash($password, $salt = false)
    {
        if ($salt === false) {
            return $this->hash($password);
        }
        if ($salt === true) {
            $salt = self::DEFAULT_SALT_LENGTH;
        }
        if (is_integer($salt)) {
            $salt = $this->randomGenerator->getRandomString($salt);
        }
        return $this->hash($salt . $password) . ':' . $salt;
    }

    /**
     * Hash a string
     *
     * @param string $data
     * @param int $version
     * @return string
     */
    public function hash($data, $version = self::HASH_VERSION_LATEST)
    {
        if (self::HASH_VERSION_MD5 === $version) {
            return md5($data);
        }
        return hash('sha256', $data);
    }

    /**
     * Validate hash against hashing method (with or without salt)
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function validateHash($password, $hash)
    {
        return $this->validateHashByVersion(
            $password,
            $hash,
            self::HASH_VERSION_SHA256
        ) || $this->validateHashByVersion(
            $password,
            $hash,
            self::HASH_VERSION_MD5
        );
    }

    /**
     * Validate hash by specified version
     *
     * @param string $password
     * @param string $hash
     * @param int $version
     * @return bool
     */
    public function validateHashByVersion($password, $hash, $version = self::HASH_VERSION_LATEST)
    {
        // look for salt
        $hashArr = explode(':', $hash, 2);
        if (1 === count($hashArr)) {
            return $this->hash($password, $version) === $hash;
        }
        list($hash, $salt) = $hashArr;
        return $this->hash($salt . $password, $version) === $hash;
    }

    /**
     * Prepend key and cipher versions to encrypted data after encrypting
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        $crypt = $this->getCrypt();
        if (null === $crypt) {
            return $data;
        }
        return $this->keyVersion . ':' . $this->cipher . ':' . (MCRYPT_MODE_CBC ===
        $crypt->getMode() ? $crypt->getInitVector() . ':' : '') . base64_encode(
            $crypt->encrypt((string)$data)
        );
    }

    /**
     * Look for key and crypt versions in encrypted data before decrypting
     *
     * Unsupported/unspecified key version silently fallback to the oldest we have
     * Unsupported cipher versions eventually throw exception
     * Unspecified cipher version fallback to the oldest we support
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data)
    {
        if ($data) {
            $parts = explode(':', $data, 4);
            $partsCount = count($parts);

            $initVector = false;
            // specified key, specified crypt, specified iv
            if (4 === $partsCount) {
                list($keyVersion, $cryptVersion, $iv, $data) = $parts;
                $initVector = $iv ? $iv : false;
                $keyVersion = (int)$keyVersion;
                $cryptVersion = self::CIPHER_RIJNDAEL_256;
                // specified key, specified crypt
            } elseif (3 === $partsCount) {
                list($keyVersion, $cryptVersion, $data) = $parts;
                $keyVersion = (int)$keyVersion;
                $cryptVersion = (int)$cryptVersion;
                // no key version = oldest key, specified crypt
            } elseif (2 === $partsCount) {
                list($cryptVersion, $data) = $parts;
                $keyVersion = 0;
                $cryptVersion = (int)$cryptVersion;
                // no key version = oldest key, no crypt version = oldest crypt
            } elseif (1 === $partsCount) {
                $keyVersion = 0;
                $cryptVersion = self::CIPHER_BLOWFISH;
                // not supported format
            } else {
                return '';
            }
            // no key for decryption
            if (!isset($this->keys[$keyVersion])) {
                return '';
            }
            $crypt = $this->getCrypt($this->keys[$keyVersion], $cryptVersion, $initVector);
            if (null === $crypt) {
                return '';
            }
            return trim($crypt->decrypt(base64_decode((string)$data)));
        }
        return '';
    }

    /**
     * Return crypt model, instantiate if it is empty
     *
     * @param string|null $key NULL value means usage of the default key specified on constructor
     * @return \Magento\Framework\Encryption\Crypt
     * @throws \Exception
     */
    public function validateKey($key)
    {
        if (preg_match('/\s/s', $key)) {
            throw new \Exception(__('The encryption key format is invalid.'));
        }
        return $this->getCrypt($key);
    }

    /**
     * Attempt to append new key & version
     *
     * @param string $key
     * @return $this
     */
    public function setNewKey($key)
    {
        $this->validateKey($key);
        $this->keys[] = $key;
        $this->keyVersion += 1;
        return $this;
    }

    /**
     * Export current keys as string
     *
     * @return string
     */
    public function exportKeys()
    {
        return implode("\n", $this->keys);
    }

    /**
     * Initialize crypt module if needed
     *
     * By default initializes with latest key and crypt versions
     *
     * @param string $key
     * @param int $cipherVersion
     * @param bool $initVector
     * @return Crypt|null
     */
    protected function getCrypt($key = null, $cipherVersion = null, $initVector = true)
    {
        if (null === $key && null === $cipherVersion) {
            $cipherVersion = self::CIPHER_RIJNDAEL_256;
        }

        if (null === $key) {
            $key = $this->keys[$this->keyVersion];
        }

        if (!$key) {
            return null;
        }

        if (null === $cipherVersion) {
            $cipherVersion = $this->cipher;
        }
        $cipherVersion = $this->validateCipher($cipherVersion);

        if ($cipherVersion === self::CIPHER_RIJNDAEL_128) {
            $cipher = MCRYPT_RIJNDAEL_128;
            $mode = MCRYPT_MODE_ECB;
        } elseif ($cipherVersion === self::CIPHER_RIJNDAEL_256) {
            $cipher = MCRYPT_RIJNDAEL_256;
            $mode = MCRYPT_MODE_CBC;
        } else {
            $cipher = MCRYPT_BLOWFISH;
            $mode = MCRYPT_MODE_ECB;
        }

        return new Crypt($key, $cipher, $mode, $initVector);
    }
}
