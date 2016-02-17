<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\PublicKey\Rsa;

/**
 * RSA private key
 */
class PrivateKey extends AbstractKey
{
    /**
     * Public key
     *
     * @var PublicKey
     */
    protected $publicKey = null;

    /**
     * Create private key instance from PEM formatted key file
     *
     * @param  string      $pemFile
     * @param  string|null $passPhrase
     * @return PrivateKey
     * @throws Exception\InvalidArgumentException
     */
    public static function fromFile($pemFile, $passPhrase = null)
    {
        if (!is_readable($pemFile)) {
            throw new Exception\InvalidArgumentException(
                "PEM file '{$pemFile}' is not readable"
            );
        }

        return new static(file_get_contents($pemFile), $passPhrase);
    }

    /**
     * Constructor
     *
     * @param  string $pemString
     * @param  string $passPhrase
     * @throws Exception\RuntimeException
     */
    public function __construct($pemString, $passPhrase = null)
    {
        $result = openssl_pkey_get_private($pemString, $passPhrase);
        if (false === $result) {
            throw new Exception\RuntimeException(
                'Unable to load private key; openssl ' . openssl_error_string()
            );
        }

        $this->pemString          = $pemString;
        $this->opensslKeyResource = $result;
        $this->details            = openssl_pkey_get_details($this->opensslKeyResource);
    }

    /**
     * Get the public key
     *
     * @return PublicKey
     */
    public function getPublicKey()
    {
        if ($this->publicKey === null) {
            $this->publicKey = new PublicKey($this->details['key']);
        }

        return $this->publicKey;
    }

    /**
     * Encrypt using this key
     *
     * @param  string $data
     * @param  integer $padding
     * @return string
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function encrypt($data, $padding = OPENSSL_PKCS1_PADDING)
    {
        if (empty($data)) {
            throw new Exception\InvalidArgumentException('The data to encrypt cannot be empty');
        }

        $encrypted = '';
        $result = openssl_private_encrypt($data, $encrypted, $this->getOpensslKeyResource(), $padding);
        if (false === $result) {
            throw new Exception\RuntimeException(
                'Can not encrypt; openssl ' . openssl_error_string()
            );
        }

        return $encrypted;
    }

    /**
     * Decrypt using this key
     * Starting in 2.4.9/2.5.2, we changed the default padding to
     * OPENSSL_PKCS1_OAEP_PADDING to prevent Bleichenbacher's chosen-ciphertext
     * attack.
     *
     * @see http://archiv.infsec.ethz.ch/education/fs08/secsem/bleichenbacher98.pdf
     * @param  string $data
     * @param  integer $padding
     * @return string
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function decrypt($data, $padding = OPENSSL_PKCS1_OAEP_PADDING)
    {
        if (!is_string($data)) {
            throw new Exception\InvalidArgumentException('The data to decrypt must be a string');
        }
        if ('' === $data) {
            throw new Exception\InvalidArgumentException('The data to decrypt cannot be empty');
        }

        $decrypted = '';
        $result = openssl_private_decrypt($data, $decrypted, $this->getOpensslKeyResource(), $padding);
        if (false === $result) {
            throw new Exception\RuntimeException(
                'Can not decrypt; openssl ' . openssl_error_string()
            );
        }

        return $decrypted;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->pemString;
    }
}
