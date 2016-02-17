<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter\Encrypt;

use Traversable;
use Zend\Filter\Compress;
use Zend\Filter\Decompress;
use Zend\Filter\Exception;
use Zend\Stdlib\ArrayUtils;

/**
 * Encryption adapter for openssl
 */
class Openssl implements EncryptionAlgorithmInterface
{
    /**
     * Definitions for encryption
     * array(
     *     'public'   => public keys
     *     'private'  => private keys
     *     'envelope' => resulting envelope keys
     * )
     */
    protected $keys = array(
        'public'   => array(),
        'private'  => array(),
        'envelope' => array(),
    );

    /**
     * Internal passphrase
     *
     * @var string
     */
    protected $passphrase;

    /**
     * Internal compression
     *
     * @var array
     */
    protected $compression;

    /**
     * Internal create package
     *
     * @var bool
     */
    protected $package = false;

    /**
     * Class constructor
     * Available options
     *   'public'      => public key
     *   'private'     => private key
     *   'envelope'    => envelope key
     *   'passphrase'  => passphrase
     *   'compression' => compress value with this compression adapter
     *   'package'     => pack envelope keys into encrypted string, simplifies decryption
     *
     * @param string|array|Traversable $options Options for this adapter
     * @throws Exception\ExtensionNotLoadedException
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('openssl')) {
            throw new Exception\ExtensionNotLoadedException('This filter needs the openssl extension');
        }

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            $options = array('public' => $options);
        }

        if (array_key_exists('passphrase', $options)) {
            $this->setPassphrase($options['passphrase']);
            unset($options['passphrase']);
        }

        if (array_key_exists('compression', $options)) {
            $this->setCompression($options['compression']);
            unset($options['compress']);
        }

        if (array_key_exists('package', $options)) {
            $this->setPackage($options['package']);
            unset($options['package']);
        }

        $this->_setKeys($options);
    }

    /**
     * Sets the encryption keys
     *
     * @param  string|array $keys Key with type association
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    protected function _setKeys($keys)
    {
        if (!is_array($keys)) {
            throw new Exception\InvalidArgumentException('Invalid options argument provided to filter');
        }

        foreach ($keys as $type => $key) {
            if (is_file($key) and is_readable($key)) {
                $file = fopen($key, 'r');
                $cert = fread($file, 8192);
                fclose($file);
            } else {
                $cert = $key;
                $key  = count($this->keys[$type]);
            }

            switch ($type) {
                case 'public':
                    $test = openssl_pkey_get_public($cert);
                    if ($test === false) {
                        throw new Exception\InvalidArgumentException("Public key '{$cert}' not valid");
                    }

                    openssl_free_key($test);
                    $this->keys['public'][$key] = $cert;
                    break;
                case 'private':
                    $test = openssl_pkey_get_private($cert, $this->passphrase);
                    if ($test === false) {
                        throw new Exception\InvalidArgumentException("Private key '{$cert}' not valid");
                    }

                    openssl_free_key($test);
                    $this->keys['private'][$key] = $cert;
                    break;
                case 'envelope':
                    $this->keys['envelope'][$key] = $cert;
                    break;
                default:
                    break;
            }
        }

        return $this;
    }

    /**
     * Returns all public keys
     *
     * @return array
     */
    public function getPublicKey()
    {
        $key = $this->keys['public'];
        return $key;
    }

    /**
     * Sets public keys
     *
     * @param  string|array $key Public keys
     * @return self
     */
    public function setPublicKey($key)
    {
        if (is_array($key)) {
            foreach ($key as $type => $option) {
                if ($type !== 'public') {
                    $key['public'] = $option;
                    unset($key[$type]);
                }
            }
        } else {
            $key = array('public' => $key);
        }

        return $this->_setKeys($key);
    }

    /**
     * Returns all private keys
     *
     * @return array
     */
    public function getPrivateKey()
    {
        $key = $this->keys['private'];
        return $key;
    }

    /**
     * Sets private keys
     *
     * @param  string $key Private key
     * @param  string $passphrase
     * @return self
     */
    public function setPrivateKey($key, $passphrase = null)
    {
        if (is_array($key)) {
            foreach ($key as $type => $option) {
                if ($type !== 'private') {
                    $key['private'] = $option;
                    unset($key[$type]);
                }
            }
        } else {
            $key = array('private' => $key);
        }

        if ($passphrase !== null) {
            $this->setPassphrase($passphrase);
        }

        return $this->_setKeys($key);
    }

    /**
     * Returns all envelope keys
     *
     * @return array
     */
    public function getEnvelopeKey()
    {
        $key = $this->keys['envelope'];
        return $key;
    }

    /**
     * Sets envelope keys
     *
     * @param  string|array $key Envelope keys
     * @return self
     */
    public function setEnvelopeKey($key)
    {
        if (is_array($key)) {
            foreach ($key as $type => $option) {
                if ($type !== 'envelope') {
                    $key['envelope'] = $option;
                    unset($key[$type]);
                }
            }
        } else {
            $key = array('envelope' => $key);
        }

        return $this->_setKeys($key);
    }

    /**
     * Returns the passphrase
     *
     * @return string
     */
    public function getPassphrase()
    {
        return $this->passphrase;
    }

    /**
     * Sets a new passphrase
     *
     * @param string $passphrase
     * @return self
     */
    public function setPassphrase($passphrase)
    {
        $this->passphrase = $passphrase;
        return $this;
    }

    /**
     * Returns the compression
     *
     * @return array
     */
    public function getCompression()
    {
        return $this->compression;
    }

    /**
     * Sets an internal compression for values to encrypt
     *
     * @param string|array $compression
     * @return self
     */
    public function setCompression($compression)
    {
        if (is_string($this->compression)) {
            $compression = array('adapter' => $compression);
        }

        $this->compression = $compression;
        return $this;
    }

    /**
     * Returns if header should be packaged
     *
     * @return bool
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Sets if the envelope keys should be included in the encrypted value
     *
     * @param  bool $package
     * @return self
     */
    public function setPackage($package)
    {
        $this->package = (bool) $package;
        return $this;
    }

    /**
     * Encrypts $value with the defined settings
     * Note that you also need the "encrypted" keys to be able to decrypt
     *
     * @param  string $value Content to encrypt
     * @return string The encrypted content
     * @throws Exception\RuntimeException
     */
    public function encrypt($value)
    {
        $encrypted     = array();
        $encryptedkeys = array();

        if (count($this->keys['public']) == 0) {
            throw new Exception\RuntimeException('Openssl can not encrypt without public keys');
        }

        $keys         = array();
        $fingerprints = array();
        $count        = -1;
        foreach ($this->keys['public'] as $key => $cert) {
            $keys[$key] = openssl_pkey_get_public($cert);
            if ($this->package) {
                $details = openssl_pkey_get_details($keys[$key]);
                if ($details === false) {
                    $details = array('key' => 'ZendFramework');
                }

                ++$count;
                $fingerprints[$count] = md5($details['key']);
            }
        }

        // compress prior to encryption
        if (!empty($this->compression)) {
            $compress = new Compress($this->compression);
            $value    = $compress($value);
        }

        $crypt  = openssl_seal($value, $encrypted, $encryptedkeys, $keys);
        foreach ($keys as $key) {
            openssl_free_key($key);
        }

        if ($crypt === false) {
            throw new Exception\RuntimeException('Openssl was not able to encrypt your content with the given options');
        }

        $this->keys['envelope'] = $encryptedkeys;

        // Pack data and envelope keys into single string
        if ($this->package) {
            $header = pack('n', count($this->keys['envelope']));
            foreach ($this->keys['envelope'] as $key => $envKey) {
                $header .= pack('H32n', $fingerprints[$key], strlen($envKey)) . $envKey;
            }

            $encrypted = $header . $encrypted;
        }

        return $encrypted;
    }

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Decrypts $value with the defined settings
     *
     * @param  string $value Content to decrypt
     * @return string The decrypted content
     * @throws Exception\RuntimeException
     */
    public function decrypt($value)
    {
        $decrypted = "";
        $envelope  = current($this->getEnvelopeKey());

        if (count($this->keys['private']) !== 1) {
            throw new Exception\RuntimeException('Please give a private key for decryption with Openssl');
        }

        if (!$this->package && empty($envelope)) {
            throw new Exception\RuntimeException('Please give an envelope key for decryption with Openssl');
        }

        foreach ($this->keys['private'] as $cert) {
            $keys = openssl_pkey_get_private($cert, $this->getPassphrase());
        }

        if ($this->package) {
            $details = openssl_pkey_get_details($keys);
            if ($details !== false) {
                $fingerprint = md5($details['key']);
            } else {
                $fingerprint = md5("ZendFramework");
            }

            $count = unpack('ncount', $value);
            $count = $count['count'];
            $length  = 2;
            for ($i = $count; $i > 0; --$i) {
                $header = unpack('H32print/nsize', substr($value, $length, 18));
                $length  += 18;
                if ($header['print'] == $fingerprint) {
                    $envelope = substr($value, $length, $header['size']);
                }

                $length += $header['size'];
            }

            // remainder of string is the value to decrypt
            $value = substr($value, $length);
        }

        $crypt  = openssl_open($value, $decrypted, $envelope, $keys);
        openssl_free_key($keys);

        if ($crypt === false) {
            throw new Exception\RuntimeException('Openssl was not able to decrypt you content with the given options');
        }

        // decompress after decryption
        if (!empty($this->compression)) {
            $decompress = new Decompress($this->compression);
            $decrypted  = $decompress($decrypted);
        }

        return $decrypted;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Openssl';
    }
}
