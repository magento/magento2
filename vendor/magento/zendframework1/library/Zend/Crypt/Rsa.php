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
 * @package    Zend_Crypt
 * @subpackage Rsa
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Crypt_Rsa_Key_Private
 */
#require_once 'Zend/Crypt/Rsa/Key/Private.php';

/**
 * @see Zend_Crypt_Rsa_Key_Public
 */
#require_once 'Zend/Crypt/Rsa/Key/Public.php';

/**
 * @category   Zend
 * @package    Zend_Crypt
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Crypt_Rsa
{

    const BINARY = 'binary';
    const BASE64 = 'base64';

    protected $_privateKey;

    protected $_publicKey;

    /**
     * @var string
     */
    protected $_pemString;

    protected $_pemPath;

    protected $_certificateString;

    protected $_certificatePath;

    protected $_hashAlgorithm;

    protected $_passPhrase;

    /**
     * Class constructor
     *
     * @param array $options
     * @throws Zend_Crypt_Rsa_Exception
     */
    public function __construct(array $options = null)
    {
        if (!extension_loaded('openssl')) {
            #require_once 'Zend/Crypt/Rsa/Exception.php';
            throw new Zend_Crypt_Rsa_Exception('Zend_Crypt_Rsa requires openssl extension to be loaded.');
        }

        // Set _hashAlgorithm property when we are sure, that openssl extension is loaded
        // and OPENSSL_ALGO_SHA1 constant is available
        $this->_hashAlgorithm = OPENSSL_ALGO_SHA1;

        if (isset($options)) {
            $this->setOptions($options);
        }
    }

    public function setOptions(array $options)
    {
        if (isset($options['passPhrase'])) {
            $this->_passPhrase = $options['passPhrase'];
        }
        foreach ($options as $option=>$value) {
            switch ($option) {
                case 'pemString':
                    $this->setPemString($value);
                    break;
                case 'pemPath':
                    $this->setPemPath($value);
                    break;
                case 'certificateString':
                    $this->setCertificateString($value);
                    break;
                case 'certificatePath':
                    $this->setCertificatePath($value);
                    break;
                case 'hashAlgorithm':
                    $this->setHashAlgorithm($value);
                    break;
            }
        }
    }

    public function getPrivateKey()
    {
        return $this->_privateKey;
    }

    public function getPublicKey()
    {
        return $this->_publicKey;
    }

    /**
     * @param string $data
     * @param Zend_Crypt_Rsa_Key_Private $privateKey
     * @param string $format
     * @return string
     */
    public function sign($data, Zend_Crypt_Rsa_Key_Private $privateKey = null, $format = null)
    {
        $signature = '';
        if (isset($privateKey)) {
            $opensslKeyResource = $privateKey->getOpensslKeyResource();
        } else {
            $opensslKeyResource = $this->_privateKey->getOpensslKeyResource();
        }
        $result = openssl_sign(
            $data, $signature,
            $opensslKeyResource,
            $this->getHashAlgorithm()
        );
        if ($format == self::BASE64) {
            return base64_encode($signature);
        }
        return $signature;
    }

    /**
     * @param string $data
     * @param string $signature
     * @param string $format
     * @return string
     */
    public function verifySignature($data, $signature, $format = null)
    {
        if ($format == self::BASE64) {
            $signature = base64_decode($signature);
        }
        $result = openssl_verify($data, $signature,
            $this->getPublicKey()->getOpensslKeyResource(),
            $this->getHashAlgorithm());
        return $result;
    }

    /**
     * @param string $data
     * @param Zend_Crypt_Rsa_Key $key
     * @param string $format
     * @return string
     */
    public function encrypt($data, Zend_Crypt_Rsa_Key $key, $format = null)
    {
        $encrypted = '';
        $function = 'openssl_public_encrypt';
        if ($key instanceof Zend_Crypt_Rsa_Key_Private) {
            $function = 'openssl_private_encrypt';
        }
        $function($data, $encrypted, $key->getOpensslKeyResource());
        if ($format == self::BASE64) {
            return base64_encode($encrypted);
        }
        return $encrypted;
    }

    /**
     * @param string $data
     * @param Zend_Crypt_Rsa_Key $key
     * @param string $format
     * @return string
     */
    public function decrypt($data, Zend_Crypt_Rsa_Key $key, $format = null)
    {
        $decrypted = '';
        if ($format == self::BASE64) {
            $data = base64_decode($data);
        }
        $function = 'openssl_private_decrypt';
        if ($key instanceof Zend_Crypt_Rsa_Key_Public) {
            $function = 'openssl_public_decrypt';
        }
        $function($data, $decrypted, $key->getOpensslKeyResource());
        return $decrypted;
    }

    /**
     * @param  array $configargs
     *
     * @throws Zend_Crypt_Rsa_Exception
     *
     * @return ArrayObject
     */
    public function generateKeys(array $configargs = null)
    {
        $config = null;
        $passPhrase = null;
        if ($configargs !== null) {
            if (isset($configargs['passPhrase'])) {
                $passPhrase = $configargs['passPhrase'];
                unset($configargs['passPhrase']);
            }
            $config = $this->_parseConfigArgs($configargs);
        }
        $privateKey = null;
        $publicKey = null;
        $resource = openssl_pkey_new($config);
        if (!$resource) {
            #require_once 'Zend/Crypt/Rsa/Exception.php';
            throw new Zend_Crypt_Rsa_Exception('Failed to generate a new private key');
        }
        // above fails on PHP 5.3
        openssl_pkey_export($resource, $private, $passPhrase);
        $privateKey = new Zend_Crypt_Rsa_Key_Private($private, $passPhrase);
        $details = openssl_pkey_get_details($resource);
        $publicKey = new Zend_Crypt_Rsa_Key_Public($details['key']);
        $return = new ArrayObject(array(
           'privateKey'=>$privateKey,
           'publicKey'=>$publicKey
        ), ArrayObject::ARRAY_AS_PROPS);
        return $return;
    }

    /**
     * @param string $value
     */
    public function setPemString($value)
    {
        $this->_pemString = $value;
        try {
            $this->_privateKey = new Zend_Crypt_Rsa_Key_Private($this->_pemString, $this->_passPhrase);
            $this->_publicKey = $this->_privateKey->getPublicKey();
        } catch (Zend_Crypt_Exception $e) {
            $this->_privateKey = null;
            $this->_publicKey = new Zend_Crypt_Rsa_Key_Public($this->_pemString);
        }
    }

    public function setPemPath($value)
    {
        $this->_pemPath = $value;
        $this->setPemString(file_get_contents($this->_pemPath));
    }

    public function setCertificateString($value)
    {
        $this->_certificateString = $value;
        $this->_publicKey = new Zend_Crypt_Rsa_Key_Public($this->_certificateString, $this->_passPhrase);
    }

    public function setCertificatePath($value)
    {
        $this->_certificatePath = $value;
        $this->setCertificateString(file_get_contents($this->_certificatePath));
    }

    public function setHashAlgorithm($name)
    {
        switch (strtolower($name)) {
            case 'md2':
                $this->_hashAlgorithm = OPENSSL_ALGO_MD2;
                break;
            case 'md4':
                $this->_hashAlgorithm = OPENSSL_ALGO_MD4;
                break;
            case 'md5':
                $this->_hashAlgorithm = OPENSSL_ALGO_MD5;
                break;
            case 'sha1':
                $this->_hashAlgorithm = OPENSSL_ALGO_SHA1;
                break;
            case 'dss1':
                $this->_hashAlgorithm = OPENSSL_ALGO_DSS1;
                break;
        }
    }

    /**
     * @return string
     */
    public function getPemString()
    {
        return $this->_pemString;
    }

    public function getPemPath()
    {
        return $this->_pemPath;
    }

    public function getCertificateString()
    {
        return $this->_certificateString;
    }

    public function getCertificatePath()
    {
        return $this->_certificatePath;
    }

    public function getHashAlgorithm()
    {
        return $this->_hashAlgorithm;
    }

    protected function _parseConfigArgs(array $config = null)
    {
        $configs = array();
        if (isset($config['private_key_bits'])) {
            $configs['private_key_bits'] = $config['private_key_bits'];
        }
        if (isset($config['privateKeyBits'])) {
            $configs['private_key_bits'] = $config['privateKeyBits'];
        }
        if (!empty($configs)) {
            return $configs;
        }
        return null;
    }

}
