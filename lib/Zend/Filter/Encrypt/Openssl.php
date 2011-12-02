<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Openssl.php 21212 2010-02-27 17:33:27Z thomas $
 */

/**
 * @see Zend_Filter_Encrypt_Interface
 */
#require_once 'Zend/Filter/Encrypt/Interface.php';

/**
 * Encryption adapter for openssl
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Encrypt_Openssl implements Zend_Filter_Encrypt_Interface
{
    /**
     * Definitions for encryption
     * array(
     *     'public'   => public keys
     *     'private'  => private keys
     *     'envelope' => resulting envelope keys
     * )
     */
    protected $_keys = array(
        'public'   => array(),
        'private'  => array(),
        'envelope' => array()
    );

    /**
     * Internal passphrase
     *
     * @var string
     */
    protected $_passphrase;

    /**
     * Internal compression
     *
     * @var array
     */
    protected $_compression;

    /**
     * Internal create package
     *
     * @var boolean
     */
    protected $_package = false;

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
     * @param string|array $options Options for this adapter
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('openssl')) {
            #require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('This filter needs the openssl extension');
        }

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
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
     * @return Zend_Filter_Encrypt_Openssl
     */
    protected function _setKeys($keys)
    {
        if (!is_array($keys)) {
            #require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Invalid options argument provided to filter');
        }

        foreach ($keys as $type => $key) {
            if (is_file($key) and is_readable($key)) {
                $file = fopen($key, 'r');
                $cert = fread($file, 8192);
                fclose($file);
            } else {
                $cert = $key;
                $key  = count($this->_keys[$type]);
            }

            switch ($type) {
                case 'public':
                    $test = openssl_pkey_get_public($cert);
                    if ($test === false) {
                        #require_once 'Zend/Filter/Exception.php';
                        throw new Zend_Filter_Exception("Public key '{$cert}' not valid");
                    }

                    openssl_free_key($test);
                    $this->_keys['public'][$key] = $cert;
                    break;
                case 'private':
                    $test = openssl_pkey_get_private($cert, $this->_passphrase);
                    if ($test === false) {
                        #require_once 'Zend/Filter/Exception.php';
                        throw new Zend_Filter_Exception("Private key '{$cert}' not valid");
                    }

                    openssl_free_key($test);
                    $this->_keys['private'][$key] = $cert;
                    break;
                case 'envelope':
                    $this->_keys['envelope'][$key] = $cert;
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
        $key = $this->_keys['public'];
        return $key;
    }

    /**
     * Sets public keys
     *
     * @param  string|array $key Public keys
     * @return Zend_Filter_Encrypt_Openssl
     */
    public function setPublicKey($key)
    {
        if (is_array($key)) {
            foreach($key as $type => $option) {
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
        $key = $this->_keys['private'];
        return $key;
    }

    /**
     * Sets private keys
     *
     * @param  string $key Private key
     * @param  string $passphrase
     * @return Zend_Filter_Encrypt_Openssl
     */
    public function setPrivateKey($key, $passphrase = null)
    {
        if (is_array($key)) {
            foreach($key as $type => $option) {
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
        $key = $this->_keys['envelope'];
        return $key;
    }

    /**
     * Sets envelope keys
     *
     * @param  string|array $options Envelope keys
     * @return Zend_Filter_Encrypt_Openssl
     */
    public function setEnvelopeKey($key)
    {
        if (is_array($key)) {
            foreach($key as $type => $option) {
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
        return $this->_passphrase;
    }

    /**
     * Sets a new passphrase
     *
     * @param string $passphrase
     * @return Zend_Filter_Encrypt_Openssl
     */
    public function setPassphrase($passphrase)
    {
        $this->_passphrase = $passphrase;
        return $this;
    }

    /**
     * Returns the compression
     *
     * @return array
     */
    public function getCompression()
    {
        return $this->_compression;
    }

    /**
     * Sets a internal compression for values to encrypt
     *
     * @param string|array $compression
     * @return Zend_Filter_Encrypt_Openssl
     */
    public function setCompression($compression)
    {
        if (is_string($this->_compression)) {
            $compression = array('adapter' => $compression);
        }

        $this->_compression = $compression;
        return $this;
    }

    /**
     * Returns if header should be packaged
     *
     * @return boolean
     */
    public function getPackage()
    {
        return $this->_package;
    }

    /**
     * Sets if the envelope keys should be included in the encrypted value
     *
     * @param boolean $package
     * @return Zend_Filter_Encrypt_Openssl
     */
    public function setPackage($package)
    {
        $this->_package = (boolean) $package;
        return $this;
    }

    /**
     * Encrypts $value with the defined settings
     * Note that you also need the "encrypted" keys to be able to decrypt
     *
     * @param  string $value Content to encrypt
     * @return string The encrypted content
     * @throws Zend_Filter_Exception
     */
    public function encrypt($value)
    {
        $encrypted     = array();
        $encryptedkeys = array();

        if (count($this->_keys['public']) == 0) {
            #require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Openssl can not encrypt without public keys');
        }

        $keys         = array();
        $fingerprints = array();
        $count        = -1;
        foreach($this->_keys['public'] as $key => $cert) {
            $keys[$key] = openssl_pkey_get_public($cert);
            if ($this->_package) {
                $details = openssl_pkey_get_details($keys[$key]);
                if ($details === false) {
                    $details = array('key' => 'ZendFramework');
                }

                ++$count;
                $fingerprints[$count] = md5($details['key']);
            }
        }

        // compress prior to encryption
        if (!empty($this->_compression)) {
            #require_once 'Zend/Filter/Compress.php';
            $compress = new Zend_Filter_Compress($this->_compression);
            $value    = $compress->filter($value);
        }

        $crypt  = openssl_seal($value, $encrypted, $encryptedkeys, $keys);
        foreach ($keys as $key) {
            openssl_free_key($key);
        }

        if ($crypt === false) {
            #require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Openssl was not able to encrypt your content with the given options');
        }

        $this->_keys['envelope'] = $encryptedkeys;

        // Pack data and envelope keys into single string
        if ($this->_package) {
            $header = pack('n', count($this->_keys['envelope']));
            foreach($this->_keys['envelope'] as $key => $envKey) {
                $header .= pack('H32n', $fingerprints[$key], strlen($envKey)) . $envKey;
            }

            $encrypted = $header . $encrypted;
        }

        return $encrypted;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Decrypts $value with the defined settings
     *
     * @param  string $value Content to decrypt
     * @return string The decrypted content
     * @throws Zend_Filter_Exception
     */
    public function decrypt($value)
    {
        $decrypted = "";
        $envelope  = current($this->getEnvelopeKey());

        if (count($this->_keys['private']) !== 1) {
            #require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Please give a private key for decryption with Openssl');
        }

        if (!$this->_package && empty($envelope)) {
            #require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Please give a envelope key for decryption with Openssl');
        }

        foreach($this->_keys['private'] as $key => $cert) {
            $keys = openssl_pkey_get_private($cert, $this->getPassphrase());
        }

        if ($this->_package) {
            $details = openssl_pkey_get_details($keys);
            if ($details !== false) {
                $fingerprint = md5($details['key']);
            } else {
                $fingerprint = md5("ZendFramework");
            }

            $count = unpack('ncount', $value);
            $count = $count['count'];
            $length  = 2;
            for($i = $count; $i > 0; --$i) {
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
            #require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Openssl was not able to decrypt you content with the given options');
        }

        // decompress after decryption
        if (!empty($this->_compression)) {
            #require_once 'Zend/Filter/Decompress.php';
            $decompress = new Zend_Filter_Decompress($this->_compression);
            $decrypted  = $decompress->filter($decrypted);
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
