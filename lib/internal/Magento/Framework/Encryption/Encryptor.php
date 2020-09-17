<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Encryption;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\Adapter\EncryptionAdapterInterface;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Math\Random;
use Magento\Framework\Encryption\Adapter\SodiumChachaIetf;
use Magento\Framework\Encryption\Adapter\Mcrypt;

/**
 * Class Encryptor provides basic logic for hashing strings and encrypting/decrypting misc data.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Encryptor implements EncryptorInterface
{
    /**
     * Key of md5 algorithm
     */
    const HASH_VERSION_MD5 = 0;

    /**
     * Key of sha256 algorithm
     */
    const HASH_VERSION_SHA256 = 1;

    /**
     * Key of Argon2ID13 algorithm
     */
    public const HASH_VERSION_ARGON2ID13 = 2;

    /**
     * Key of Argon2ID13 algorithm that works on any PHP and libsodium version.
     */
    public const HASH_VERSION_ARGON2ID13_AGNOSTIC = 3;

    /**
     * Key of latest used algorithm
     *
     * @deprecated Latest version is dynamic based on current setup.
     * @see \Magento\Framework\Encryption\Encryptor::getLatestHashVersion
     */
    const HASH_VERSION_LATEST = 3;

    /**
     * Default length of salt in bytes
     */
    const DEFAULT_SALT_LENGTH = 32;

    /**#@+
     * Exploded password hash keys
     */
    const PASSWORD_HASH = 0;
    const PASSWORD_SALT = 1;
    const PASSWORD_VERSION = 2;
    /**#@-*/

    /**
     * Array key of encryption key in deployment config
     */
    const PARAM_CRYPT_KEY = 'crypt/key';

    /**#@+
     * Cipher versions
     */
    const CIPHER_BLOWFISH = 0;

    const CIPHER_RIJNDAEL_128 = 1;

    const CIPHER_RIJNDAEL_256 = 2;

    const CIPHER_AEAD_CHACHA20POLY1305 = 3;

    const CIPHER_LATEST = 3;
    /**#@-*/

    /**
     * Default hash string delimiter
     */
    const DELIMITER = ':';

    /**
     * Map of simple hash versions
     *
     * @var array
     */
    private $hashVersionMap = [
        self::HASH_VERSION_MD5 => 'md5',
        self::HASH_VERSION_SHA256 => 'sha256'
    ];

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
     * @var Random
     */
    private $random;

    /**
     * @var KeyValidator
     */
    private $keyValidator;

    /**
     * Encryptor constructor.
     *
     * @param Random $random
     * @param DeploymentConfig $deploymentConfig
     * @param KeyValidator|null $keyValidator
     */
    public function __construct(
        Random $random,
        DeploymentConfig $deploymentConfig,
        KeyValidator $keyValidator = null
    ) {
        $this->random = $random;

        // load all possible keys
        $this->keys = preg_split('/\s+/s', trim((string)$deploymentConfig->get(self::PARAM_CRYPT_KEY)));
        $this->keyVersion = count($this->keys) - 1;
        $this->keyValidator = $keyValidator ?: ObjectManager::getInstance()->get(KeyValidator::class);
    }

    /**
     * Gets latest hash algorithm version.
     *
     * @return int
     */
    public function getLatestHashVersion(): int
    {
        return self::HASH_VERSION_ARGON2ID13_AGNOSTIC;
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
        $types = [
            self::CIPHER_BLOWFISH,
            self::CIPHER_RIJNDAEL_128,
            self::CIPHER_RIJNDAEL_256,
            self::CIPHER_AEAD_CHACHA20POLY1305,
        ];

        $version = (int)$version;
        if (!in_array($version, $types, true)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception((string)new \Magento\Framework\Phrase('Not supported cipher version'));
        }
        return $version;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getHash($password, $salt = false, $version = self::HASH_VERSION_LATEST)
    {
        if ($version < 0 || $version > $this->getLatestHashVersion()) {
            $version = $this->getLatestHashVersion();
        }
        $isArgon = $version === self::HASH_VERSION_ARGON2ID13 || $version === self::HASH_VERSION_ARGON2ID13_AGNOSTIC;

        if ($salt === false) {
            //Generating a simple hash without salt.
            if ($isArgon) {
                $version = self::HASH_VERSION_SHA256;
            }

            return $this->hash($password, $version);
        }
        if ($salt === true) {
            //Generate random default length salt
            $salt = self::DEFAULT_SALT_LENGTH;
        }
        if (is_integer($salt)) {
            //Generate salt of given length.
            $salt = $this->random->getRandomString($salt);
        }

        if ($isArgon) {
            $seedBytes = SODIUM_CRYPTO_SIGN_SEEDBYTES;
            $opsLimit = SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE;
            $memLimit = SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE;
            if ($version === self::HASH_VERSION_ARGON2ID13_AGNOSTIC) {
                $version = implode('_', [self::HASH_VERSION_ARGON2ID13_AGNOSTIC, $seedBytes, $opsLimit, $memLimit]);
            }

            $hash = $this->getArgonHash($password, $seedBytes, $opsLimit, $memLimit, $salt);
        } else {
            $hash = $this->generateSimpleHash($salt . $password, (int)$version);
        }

        return implode(
            self::DELIMITER,
            [
                $hash,
                $salt,
                $version
            ]
        );
    }

    /**
     * Generate simple hash for given string.
     *
     * @param string $data
     * @param int $version
     * @return string
     */
    private function generateSimpleHash(string $data, int $version): string
    {
        if (!array_key_exists($version, $this->hashVersionMap)) {
            throw new \InvalidArgumentException('Unknown hashing algorithm');
        }

        return hash($this->hashVersionMap[$version], (string)$data);
    }

    /**
     * @inheritdoc
     */
    public function hash($data, $version = self::HASH_VERSION_SHA256)
    {
        if (empty($this->keys[$this->keyVersion])) {
            throw new \RuntimeException('No key available');
        }
        if (!array_key_exists($version, $this->hashVersionMap)) {
            throw new \InvalidArgumentException('Unknown hashing algorithm');
        }

        return hash_hmac($this->hashVersionMap[$version], (string)$data, $this->keys[$this->keyVersion], false);
    }

    /**
     * @inheritdoc
     */
    public function validateHash($password, $hash)
    {
        return $this->isValidHash($password, $hash);
    }

    /**
     * @inheritdoc
     */
    public function isValidHash($password, $hash)
    {
        $agnosticArgonRegEx = '/^' .self::HASH_VERSION_ARGON2ID13_AGNOSTIC
            .'\_(?<seed>\d+)\_(?<ops>\d+)\_(?<mem>\d+)$/';
        try {
            [$hash, $hashSalt, $hashVersions] = $this->explodePasswordHash($hash);
            $recreated = $password;
            //Upgraded hashes would have been hashed with multiple algorithms.
            //Hashing the test string with every algorithm the original string has been hashed with.
            foreach ($hashVersions as $hashVersion) {
                if (is_string($hashVersion) && preg_match($agnosticArgonRegEx, $hashVersion, $argonParams)) {
                    $recreated = $this->getArgonHash(
                        $recreated,
                        (int)$argonParams['seed'],
                        (int)$argonParams['ops'],
                        (int)$argonParams['mem'],
                        $hashSalt
                    );
                } elseif ((int)$hashVersion === self::HASH_VERSION_ARGON2ID13) {
                    $recreated = $this->getArgonHash(
                        $recreated,
                        SODIUM_CRYPTO_SIGN_SEEDBYTES,
                        SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
                        SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
                        $hashSalt
                    );
                } else {
                    $recreated = $this->generateSimpleHash($hashSalt . $recreated, (int)$hashVersion);
                }
            }
        } catch (\Throwable $exception) {
            //Hash is not a password hash.
            $recreated = $this->hash($password);
        }

        return Security::compareStrings(
            $recreated,
            $hash
        );
    }

    /**
     * @inheritdoc
     */
    public function validateHashVersion($hash, $validateCount = false)
    {
        try {
            $hashVersions = $this->explodePasswordHash($hash)[2];
        } catch (\RuntimeException $exception) {
            //Not a password hash.
            return true;
        }
        if ($this->getLatestHashVersion() === self::HASH_VERSION_ARGON2ID13_AGNOSTIC) {
            //Agnostic Argon also stores Argon parameters.
            $validVersion = preg_match(
                '/^' .self::HASH_VERSION_ARGON2ID13_AGNOSTIC .'\_\d+\_\d+\_\d+$/',
                end($hashVersions)
            );
        } else {
            $validVersion = end($hashVersions) === $this->getLatestHashVersion();
        }

        return $validVersion && (!$validateCount || count($hashVersions) === 1);
    }

    /**
     * Explode password hash
     *
     * @param string $hash
     * @return array
     * @throws \RuntimeException When given hash cannot be processed.
     */
    private function explodePasswordHash($hash)
    {
        $explodedPassword = explode(self::DELIMITER, $hash, 3);
        if (count($explodedPassword) !== 3) {
            throw new \RuntimeException('Hash is not a password hash');
        }

        //Hashes that have been upgraded will have algorithm version history starting from the oldest one used.
        $explodedPassword[self::PASSWORD_VERSION] = explode(
            self::DELIMITER,
            $explodedPassword[self::PASSWORD_VERSION]
        );

        return $explodedPassword;
    }

    /**
     * Prepend key and cipher versions to encrypted data after encrypting
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data)
    {
        $crypt = new SodiumChachaIetf($this->keys[$this->keyVersion]);

        return $this->keyVersion .
            ':' . self::CIPHER_AEAD_CHACHA20POLY1305 .
            ':' . base64_encode($crypt->encrypt($data));
    }

    /**
     * Encrypt data using the fastest available algorithm
     *
     * @param string $data
     * @return string
     */
    public function encryptWithFastestAvailableAlgorithm($data)
    {
        $crypt = $this->getCrypt();
        if (null === $crypt) {
            return $data;
        }
        return $this->keyVersion .
            ':' . $this->getCipherVersion() .
            ':' . base64_encode($crypt->encrypt($data));
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
     * @throws \Exception
     */
    public function decrypt($data)
    {
        if ($data) {
            $parts = explode(':', $data, 4);
            $partsCount = count($parts);

            $initVector = null;
            // specified key, specified crypt, specified iv
            if (4 === $partsCount) {
                list($keyVersion, $cryptVersion, $iv, $data) = $parts;
                $initVector = $iv ? $iv : null;
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
     * Validate key contains only allowed characters
     *
     * @param string|null $key NULL value means usage of the default key specified on constructor
     * @throws \Exception
     */
    public function validateKey($key)
    {
        if (!$this->keyValidator->isValid($key)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception(
                (string)new \Magento\Framework\Phrase(
                    'Encryption key must be 32 character string without any white space.'
                )
            );
        }
    }

    /**
     * Attempt to append new key & version
     *
     * @param string $key
     * @return $this
     * @throws \Exception
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
     * @param string $initVector
     * @return EncryptionAdapterInterface|null
     * @throws \Exception
     */
    private function getCrypt(
        string $key = null,
        int $cipherVersion = null,
        string $initVector = null
    ): ?EncryptionAdapterInterface {
        //phpcs:disable PHPCompatibility.Constants.RemovedConstants
        if (null === $key && null === $cipherVersion) {
            $cipherVersion = $this->getCipherVersion();
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

        if ($cipherVersion >= self::CIPHER_AEAD_CHACHA20POLY1305) {
            return new SodiumChachaIetf($key);
        }

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
        //phpcs:enable PHPCompatibility.Constants.RemovedConstants

        return new Mcrypt($key, $cipher, $mode, $initVector);
    }

    /**
     * Get cipher version
     *
     * @return int
     */
    private function getCipherVersion()
    {
        return $this->cipher;
    }

    /**
     * Generate Argon2ID13 hash.
     *
     * @param string $data
     * @param int $seedBytes
     * @param int $opsLimit
     * @param int $memLimit
     * @param string $salt
     * @return string
     * @throws \SodiumException
     */
    private function getArgonHash(
        string $data,
        int $seedBytes,
        int $opsLimit,
        int $memLimit,
        string $salt
    ): string {
        if (strlen($salt) < SODIUM_CRYPTO_PWHASH_SALTBYTES) {
            $salt = str_pad($salt, SODIUM_CRYPTO_PWHASH_SALTBYTES, $salt);
        } elseif (strlen($salt) > SODIUM_CRYPTO_PWHASH_SALTBYTES) {
            $salt = substr($salt, 0, SODIUM_CRYPTO_PWHASH_SALTBYTES);
        }

        return bin2hex(
            sodium_crypto_pwhash(
                $seedBytes,
                $data,
                $salt,
                $opsLimit,
                $memLimit,
                SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13
            )
        );
    }
}
