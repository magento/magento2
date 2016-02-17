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
 * Compresses a given string
 */
class Compress extends AbstractFilter
{
    /**
     * Compression adapter
     */
    protected $adapter = 'Gz';

    /**
     * Compression adapter constructor options
     */
    protected $adapterOptions = array();

    /**
     * Class constructor
     *
     * @param string|array|Traversable $options (Optional) Options to set
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (is_string($options)) {
            $this->setAdapter($options);
        } elseif ($options instanceof Compress\CompressionAlgorithmInterface) {
            $this->setAdapter($options);
        } elseif (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Set filter setate
     *
     * @param  array $options
     * @throws Exception\InvalidArgumentException if options is not an array or Traversable
     * @return self
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"%s" expects an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        foreach ($options as $key => $value) {
            if ($key == 'options') {
                $key = 'adapterOptions';
            }
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Returns the current adapter, instantiating it if necessary
     *
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     * @return Compress\CompressionAlgorithmInterface
     */
    public function getAdapter()
    {
        if ($this->adapter instanceof Compress\CompressionAlgorithmInterface) {
            return $this->adapter;
        }

        $adapter = $this->adapter;
        $options = $this->getAdapterOptions();
        if (!class_exists($adapter)) {
            $adapter = 'Zend\\Filter\\Compress\\' . ucfirst($adapter);
            if (!class_exists($adapter)) {
                throw new Exception\RuntimeException(sprintf(
                    '%s unable to load adapter; class "%s" not found',
                    __METHOD__,
                    $this->adapter
                ));
            }
        }

        $this->adapter = new $adapter($options);
        if (!$this->adapter instanceof Compress\CompressionAlgorithmInterface) {
            throw new Exception\InvalidArgumentException("Compression adapter '" . $adapter . "' does not implement Zend\\Filter\\Compress\\CompressionAlgorithmInterface");
        }
        return $this->adapter;
    }

    /**
     * Retrieve adapter name
     *
     * @return string
     */
    public function getAdapterName()
    {
        return $this->getAdapter()->toString();
    }

    /**
     * Sets compression adapter
     *
     * @param  string|Compress\CompressionAlgorithmInterface $adapter Adapter to use
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setAdapter($adapter)
    {
        if ($adapter instanceof Compress\CompressionAlgorithmInterface) {
            $this->adapter = $adapter;
            return $this;
        }
        if (!is_string($adapter)) {
            throw new Exception\InvalidArgumentException('Invalid adapter provided; must be string or instance of Zend\\Filter\\Compress\\CompressionAlgorithmInterface');
        }
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Retrieve adapter options
     *
     * @return array
     */
    public function getAdapterOptions()
    {
        return $this->adapterOptions;
    }

    /**
     * Set adapter options
     *
     * @param  array $options
     * @return self
     */
    public function setAdapterOptions(array $options)
    {
        $this->adapterOptions = $options;
        return $this;
    }

    /**
     * Get individual or all options from underlying adapter
     *
     * @param  null|string $option
     * @return mixed
     */
    public function getOptions($option = null)
    {
        $adapter = $this->getAdapter();
        return $adapter->getOptions($option);
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
        $adapter = $this->getAdapter();
        if (!method_exists($adapter, $method)) {
            throw new Exception\BadMethodCallException("Unknown method '{$method}'");
        }

        return call_user_func_array(array($adapter, $method), $options);
    }

    /**
     * Defined by Zend\Filter\FilterInterface
     *
     * Compresses the content $value with the defined settings
     *
     * @param  string $value Content to compress
     * @return string The compressed content
     */
    public function filter($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        return $this->getAdapter()->compress($value);
    }
}
