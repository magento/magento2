<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\Symmetric;

use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * Symmetric encryption using the Mcrypt extension
 *
 * NOTE: DO NOT USE only this class to encrypt data.
 * This class doesn't provide authentication and integrity check over the data.
 * PLEASE USE Zend\Crypt\BlockCipher instead!
 */
class Mcrypt implements SymmetricInterface
{
    const DEFAULT_PADDING = 'pkcs7';

    /**
     * Key
     *
     * @var string
     */
    protected $key;

    /**
     * IV
     *
     * @var string
     */
    protected $iv;

    /**
     * Encryption algorithm
     *
     * @var string
     */
    protected $algo = 'aes';

    /**
     * Encryption mode
     *
     * @var string
     */
    protected $mode = 'cbc';

    /**
     * Padding
     *
     * @var Padding\PaddingInterface
     */
    protected $padding;

    /**
     * Padding plugins
     *
     * @var PaddingPluginManager
     */
    protected static $paddingPlugins = null;

    /**
     * Supported cipher algorithms
     *
     * @var array
     */
    protected $supportedAlgos = array(
        'aes'          => 'rijndael-128',
        'blowfish'     => 'blowfish',
        'des'          => 'des',
        '3des'         => 'tripledes',
        'tripledes'    => 'tripledes',
        'cast-128'     => 'cast-128',
        'cast-256'     => 'cast-256',
        'rijndael-128' => 'rijndael-128',
        'rijndael-192' => 'rijndael-192',
        'rijndael-256' => 'rijndael-256',
        'saferplus'    => 'saferplus',
        'serpent'      => 'serpent',
        'twofish'      => 'twofish'
    );

    /**
     * Supported encryption modes
     *
     * @var array
     */
    protected $supportedModes = array(
        'cbc'  => 'cbc',
        'cfb'  => 'cfb',
        'ctr'  => 'ctr',
        'ofb'  => 'ofb',
        'nofb' => 'nofb',
        'ncfb' => 'ncfb'
    );

    /**
     * Constructor
     *
     * @param  array|Traversable                  $options
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = array())
    {
        if (!extension_loaded('mcrypt')) {
            throw new Exception\RuntimeException(
                'You cannot use ' . __CLASS__ . ' without the Mcrypt extension'
            );
        }
        if (!empty($options)) {
            if ($options instanceof Traversable) {
                $options = ArrayUtils::iteratorToArray($options);
            } elseif (!is_array($options)) {
                throw new Exception\InvalidArgumentException(
                    'The options parameter must be an array, a Zend\Config\Config object or a Traversable'
                );
            }
            foreach ($options as $key => $value) {
                switch (strtolower($key)) {
                    case 'algo':
                    case 'algorithm':
                        $this->setAlgorithm($value);
                        break;
                    case 'mode':
                        $this->setMode($value);
                        break;
                    case 'key':
                        $this->setKey($value);
                        break;
                    case 'iv':
                    case 'salt':
                        $this->setSalt($value);
                        break;
                    case 'padding':
                        $plugins       = static::getPaddingPluginManager();
                        $padding       = $plugins->get($value);
                        $this->padding = $padding;
                        break;
                }
            }
        }
        $this->setDefaultOptions($options);
    }

    /**
     * Set default options
     *
     * @param  array $options
     * @return void
     */
    protected function setDefaultOptions($options = array())
    {
        if (!isset($options['padding'])) {
            $plugins       = static::getPaddingPluginManager();
            $padding       = $plugins->get(self::DEFAULT_PADDING);
            $this->padding = $padding;
        }
    }

    /**
     * Returns the padding plugin manager.  If it doesn't exist it's created.
     *
     * @return PaddingPluginManager
     */
    public static function getPaddingPluginManager()
    {
        if (static::$paddingPlugins === null) {
            self::setPaddingPluginManager(new PaddingPluginManager());
        }

        return static::$paddingPlugins;
    }

    /**
     * Set the padding plugin manager
     *
     * @param  string|PaddingPluginManager        $plugins
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public static function setPaddingPluginManager($plugins)
    {
        if (is_string($plugins)) {
            if (!class_exists($plugins)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unable to locate padding plugin manager via class "%s"; class does not exist',
                    $plugins
                ));
            }
            $plugins = new $plugins();
        }
        if (!$plugins instanceof PaddingPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Padding plugins must extend %s\PaddingPluginManager; received "%s"',
                __NAMESPACE__,
                (is_object($plugins) ? get_class($plugins) : gettype($plugins))
            ));
        }
        static::$paddingPlugins = $plugins;
    }

    /**
     * Get the maximum key size for the selected cipher and mode of operation
     *
     * @return int
     */
    public function getKeySize()
    {
        return mcrypt_get_key_size($this->supportedAlgos[$this->algo], $this->supportedModes[$this->mode]);
    }

    /**
     * Set the encryption key
     * If the key is longer than maximum supported, it will be truncated by getKey().
     *
     * @param  string                             $key
     * @throws Exception\InvalidArgumentException
     * @return Mcrypt
     */
    public function setKey($key)
    {
        $keyLen = strlen($key);

        if (!$keyLen) {
            throw new Exception\InvalidArgumentException('The key cannot be empty');
        }
        $keySizes = mcrypt_module_get_supported_key_sizes($this->supportedAlgos[$this->algo]);
        $maxKey = $this->getKeySize();

        /*
         * blowfish has $keySizes empty, meaning it can have arbitrary key length.
         * the others are more picky.
         */
        if (!empty($keySizes) && $keyLen < $maxKey) {
            if (!in_array($keyLen, $keySizes)) {
                throw new Exception\InvalidArgumentException(
                    "The size of the key must be one of " . implode(", ", $keySizes) . " bytes or longer"
                );
            }
        }
        $this->key = $key;

        return $this;
    }

    /**
     * Get the encryption key
     *
     * @return string
     */
    public function getKey()
    {
        if (empty($this->key)) {
            return;
        }
        return substr($this->key, 0, $this->getKeySize());
    }

    /**
     * Set the encryption algorithm (cipher)
     *
     * @param  string                             $algo
     * @throws Exception\InvalidArgumentException
     * @return Mcrypt
     */
    public function setAlgorithm($algo)
    {
        if (!array_key_exists($algo, $this->supportedAlgos)) {
            throw new Exception\InvalidArgumentException(
                "The algorithm $algo is not supported by " . __CLASS__
            );
        }
        $this->algo = $algo;

        return $this;
    }

    /**
     * Get the encryption algorithm
     *
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->algo;
    }

    /**
     * Set the padding object
     *
     * @param  Padding\PaddingInterface $padding
     * @return Mcrypt
     */
    public function setPadding(Padding\PaddingInterface $padding)
    {
        $this->padding = $padding;

        return $this;
    }

    /**
     * Get the padding object
     *
     * @return Padding\PaddingInterface
     */
    public function getPadding()
    {
        return $this->padding;
    }

    /**
     * Encrypt
     *
     * @param  string                             $data
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function encrypt($data)
    {
        // Cannot encrypt empty string
        if (!is_string($data) || $data === '') {
            throw new Exception\InvalidArgumentException('The data to encrypt cannot be empty');
        }
        if (null === $this->getKey()) {
            throw new Exception\InvalidArgumentException('No key specified for the encryption');
        }
        if (null === $this->getSalt()) {
            throw new Exception\InvalidArgumentException('The salt (IV) cannot be empty');
        }
        if (null === $this->getPadding()) {
            throw new Exception\InvalidArgumentException('You have to specify a padding method');
        }
        // padding
        $data = $this->padding->pad($data, $this->getBlockSize());
        $iv   = $this->getSalt();
        // encryption
        $result = mcrypt_encrypt(
            $this->supportedAlgos[$this->algo],
            $this->getKey(),
            $data,
            $this->supportedModes[$this->mode],
            $iv
        );

        return $iv . $result;
    }

    /**
     * Decrypt
     *
     * @param  string                             $data
     * @throws Exception\InvalidArgumentException
     * @return string
     */
    public function decrypt($data)
    {
        if (empty($data)) {
            throw new Exception\InvalidArgumentException('The data to decrypt cannot be empty');
        }
        if (null === $this->getKey()) {
            throw new Exception\InvalidArgumentException('No key specified for the decryption');
        }
        if (null === $this->getPadding()) {
            throw new Exception\InvalidArgumentException('You have to specify a padding method');
        }
        $iv         = substr($data, 0, $this->getSaltSize());
        $ciphertext = substr($data, $this->getSaltSize());
        $result     = mcrypt_decrypt(
            $this->supportedAlgos[$this->algo],
            $this->getKey(),
            $ciphertext,
            $this->supportedModes[$this->mode],
            $iv
        );
        // unpadding
        return $this->padding->strip($result);
    }

    /**
     * Get the salt (IV) size
     *
     * @return int
     */
    public function getSaltSize()
    {
        return mcrypt_get_iv_size($this->supportedAlgos[$this->algo], $this->supportedModes[$this->mode]);
    }

    /**
     * Get the supported algorithms
     *
     * @return array
     */
    public function getSupportedAlgorithms()
    {
        return array_keys($this->supportedAlgos);
    }

    /**
     * Set the salt (IV)
     *
     * @param  string                             $salt
     * @throws Exception\InvalidArgumentException
     * @return Mcrypt
     */
    public function setSalt($salt)
    {
        if (empty($salt)) {
            throw new Exception\InvalidArgumentException('The salt (IV) cannot be empty');
        }
        if (strlen($salt) < $this->getSaltSize()) {
            throw new Exception\InvalidArgumentException(
                'The size of the salt (IV) must be at least ' . $this->getSaltSize() . ' bytes'
            );
        }
        $this->iv = $salt;

        return $this;
    }

    /**
     * Get the salt (IV) according to the size requested by the algorithm
     *
     * @return string
     */
    public function getSalt()
    {
        if (empty($this->iv)) {
            return;
        }
        if (strlen($this->iv) < $this->getSaltSize()) {
            throw new Exception\RuntimeException(
                'The size of the salt (IV) must be at least ' . $this->getSaltSize() . ' bytes'
            );
        }

        return substr($this->iv, 0, $this->getSaltSize());
    }

    /**
     * Get the original salt value
     *
     * @return string
     */
    public function getOriginalSalt()
    {
        return $this->iv;
    }

    /**
     * Set the cipher mode
     *
     * @param  string                             $mode
     * @throws Exception\InvalidArgumentException
     * @return Mcrypt
     */
    public function setMode($mode)
    {
        if (!empty($mode)) {
            $mode = strtolower($mode);
            if (!array_key_exists($mode, $this->supportedModes)) {
                throw new Exception\InvalidArgumentException(
                    "The mode $mode is not supported by " . __CLASS__
                );
            }
            $this->mode = $mode;
        }

        return $this;
    }

    /**
     * Get the cipher mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get all supported encryption modes
     *
     * @return array
     */
    public function getSupportedModes()
    {
        return array_keys($this->supportedModes);
    }

    /**
     * Get the block size
     *
     * @return int
     */
    public function getBlockSize()
    {
        return mcrypt_get_block_size($this->supportedAlgos[$this->algo], $this->supportedModes[$this->mode]);
    }
}
