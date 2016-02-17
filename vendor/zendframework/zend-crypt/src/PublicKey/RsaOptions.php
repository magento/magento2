<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\PublicKey;

use Zend\Crypt\PublicKey\Rsa\Exception;
use Zend\Stdlib\AbstractOptions;

/**
 * RSA instance options
 */
class RsaOptions extends AbstractOptions
{
    /**
     * @var Rsa\PrivateKey
     */
    protected $privateKey = null;

    /**
     * @var Rsa\PublicKey
     */
    protected $publicKey = null;

    /**
     * @var string
     */
    protected $hashAlgorithm = 'sha1';

    /**
     * Signature hash algorithm defined by openss constants
     *
     * @var int
     */
    protected $opensslSignatureAlgorithm = null;

    /**
     * @var string
     */
    protected $passPhrase = null;

    /**
     * Output is binary
     *
     * @var bool
     */
    protected $binaryOutput = true;

    /**
     * Set private key
     *
     * @param  Rsa\PrivateKey $key
     * @return RsaOptions
     */
    public function setPrivateKey(Rsa\PrivateKey $key)
    {
        $this->privateKey = $key;
        $this->publicKey  = $this->privateKey->getPublicKey();
        return $this;
    }

    /**
     * Get private key
     *
     * @return null|Rsa\PrivateKey
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * Set public key
     *
     * @param  Rsa\PublicKey $key
     * @return RsaOptions
     */
    public function setPublicKey(Rsa\PublicKey $key)
    {
        $this->publicKey = $key;
        return $this;
    }

    /**
     * Get public key
     *
     * @return null|Rsa\PublicKey
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Set pass phrase
     *
     * @param string $phrase
     * @return RsaOptions
     */
    public function setPassPhrase($phrase)
    {
        $this->passPhrase = (string) $phrase;
        return $this;
    }

    /**
     * Get pass phrase
     *
     * @return string
     */
    public function getPassPhrase()
    {
        return $this->passPhrase;
    }

    /**
     * Set hash algorithm
     *
     * @param  string $hash
     * @return RsaOptions
     * @throws Rsa\Exception\RuntimeException
     * @throws Rsa\Exception\InvalidArgumentException
     */
    public function setHashAlgorithm($hash)
    {
        $hashUpper = strtoupper($hash);
        if (!defined('OPENSSL_ALGO_' . $hashUpper)) {
            throw new Exception\InvalidArgumentException(
                "Hash algorithm '{$hash}' is not supported"
            );
        }

        $this->hashAlgorithm = strtolower($hash);
        $this->opensslSignatureAlgorithm = constant('OPENSSL_ALGO_' . $hashUpper);
        return $this;
    }

    /**
     * Get hash algorithm
     *
     * @return string
     */
    public function getHashAlgorithm()
    {
        return $this->hashAlgorithm;
    }

    public function getOpensslSignatureAlgorithm()
    {
        if (!isset($this->opensslSignatureAlgorithm)) {
            $this->opensslSignatureAlgorithm = constant('OPENSSL_ALGO_' . strtoupper($this->hashAlgorithm));
        }
        return $this->opensslSignatureAlgorithm;
    }

    /**
     * Enable/disable the binary output
     *
     * @param  bool $value
     * @return RsaOptions
     */
    public function setBinaryOutput($value)
    {
        $this->binaryOutput = (bool) $value;
        return $this;
    }

    /**
     * Get the value of binary output
     *
     * @return bool
     */
    public function getBinaryOutput()
    {
        return $this->binaryOutput;
    }

    /**
     * Generate new private/public key pair
     *
     * @param  array $opensslConfig
     * @return RsaOptions
     * @throws Rsa\Exception\RuntimeException
     */
    public function generateKeys(array $opensslConfig = array())
    {
        $opensslConfig = array_replace(
            array(
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
                'private_key_bits' => Rsa\PrivateKey::DEFAULT_KEY_SIZE,
                'digest_alg'       => $this->getHashAlgorithm()
            ),
            $opensslConfig
        );

        // generate
        $resource = openssl_pkey_new($opensslConfig);
        if (false === $resource) {
            throw new Exception\RuntimeException(
                'Can not generate keys; openssl ' . openssl_error_string()
            );
        }

        // export key
        $passPhrase = $this->getPassPhrase();
        $result     = openssl_pkey_export($resource, $private, $passPhrase, $opensslConfig);
        if (false === $result) {
            throw new Exception\RuntimeException(
                'Can not export key; openssl ' . openssl_error_string()
            );
        }

        $details          = openssl_pkey_get_details($resource);
        $this->privateKey = new Rsa\PrivateKey($private, $passPhrase);
        $this->publicKey  = new Rsa\PublicKey($details['key']);

        return $this;
    }
}
