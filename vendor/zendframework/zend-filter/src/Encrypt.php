<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

use Traversable;
use Zend\Stdlib\ArrayUtils;

/**
 * Encrypts a given string
 */
class Encrypt extends AbstractFilter
{
    /**
     * Encryption adapter
     *
     * @param Encrypt\EncryptionAlgorithmInterface
     */
    protected $adapter;

    /**
     * Class constructor
     *
     * @param string|array|Traversable $options (Optional) Options to set, if null mcrypt is used
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        $this->setAdapter($options);
    }

    /**
     * Returns the adapter instance
     *
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     * @return Encrypt\EncryptionAlgorithmInterface
     */
    public function getAdapterInstance()
    {
        if ($this->adapter instanceof Encrypt\EncryptionAlgorithmInterface) {
            return $this->adapter;
        }

        $adapter = $this->adapter;
        $options = $this->getOptions();
        if (! class_exists($adapter)) {
            $adapter = __CLASS__ . '\\' . ucfirst($adapter);
            if (! class_exists($adapter)) {
                throw new Exception\RuntimeException(sprintf(
                    '%s unable to load adapter; class "%s" not found',
                    __METHOD__,
                    $this->adapter
                ));
            }
        }

        $this->adapter = new $adapter($options);
        if (! $this->adapter instanceof Encrypt\EncryptionAlgorithmInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Encryption adapter "%s" does not implement %s\\EncryptionAlgorithmInterface',
                $adapter,
                __CLASS__
            ));
        }
        return $this->adapter;
    }

    /**
     * Returns the name of the set adapter
     *
     * @return string
     */
    public function getAdapter()
    {
        return $this->adapter->toString();
    }

    /**
     * Sets new encryption options
     *
     * @param  string|array $options (Optional) Encryption options
     * @return self
     * @throws Exception\DomainException
     * @throws Exception\InvalidArgumentException
     */
    public function setAdapter($options = null)
    {
        if (is_string($options)) {
            $adapter = $options;
        } elseif (isset($options['adapter'])) {
            $adapter = $options['adapter'];
            unset($options['adapter']);
        } else {
            $adapter = 'BlockCipher';
        }

        if (!is_array($options)) {
            $options = array();
        }

        if (class_exists('Zend\Filter\Encrypt\\' . ucfirst($adapter))) {
            $adapter = 'Zend\Filter\Encrypt\\' . ucfirst($adapter);
        } elseif (!class_exists($adapter)) {
            throw new Exception\DomainException(
                sprintf(
                    '%s expects a valid registry class name; received "%s", which did not resolve',
                    __METHOD__,
                    $adapter
                )
            );
        }

        $this->adapter = new $adapter($options);
        if (!$this->adapter instanceof Encrypt\EncryptionAlgorithmInterface) {
            throw new Exception\InvalidArgumentException(
                "Encoding adapter '" . $adapter
                . "' does not implement Zend\\Filter\\Encrypt\\EncryptionAlgorithmInterface"
            );
        }

        return $this;
    }

    /**
     * Calls adapter methods
     *
     * @param string       $method  Method to call
     * @param string|array $options Options for this method
     * @return mixed
     * @throws Exception\BadMethodCallException
     */
    public function __call($method, $options)
    {
        $part = substr($method, 0, 3);
        if ((($part != 'get') && ($part != 'set')) || !method_exists($this->adapter, $method)) {
            throw new Exception\BadMethodCallException("Unknown method '{$method}'");
        }

        return call_user_func_array(array($this->adapter, $method), $options);
    }

    /**
     * Defined by Zend\Filter\Filter
     *
     * Encrypts the content $value with the defined settings
     *
     * @param  string $value Content to encrypt
     * @return string The encrypted content
     */
    public function filter($value)
    {
        if (!is_string($value) && !is_numeric($value)) {
            return $value;
        }

        return $this->adapter->encrypt($value);
    }
}
