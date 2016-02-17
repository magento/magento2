<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Math\BigInteger;

abstract class BigInteger
{
    /**
     * Plugin manager for loading adapters
     *
     * @var null|AdapterPluginManager
     */
    protected static $adapters = null;

    /**
     * The default adapter.
     *
     * @var Adapter\AdapterInterface
     */
    protected static $defaultAdapter = null;

    /**
     * Create a BigInteger adapter instance
     *
     * @param  string|Adapter\AdapterInterface|null $adapterName
     * @return Adapter\AdapterInterface
     */
    public static function factory($adapterName = null)
    {
        if (null === $adapterName) {
            return static::getAvailableAdapter();
        } elseif ($adapterName instanceof Adapter\AdapterInterface) {
            return $adapterName;
        }

        return static::getAdapterPluginManager()->get($adapterName);
    }

    /**
     * Set adapter plugin manager
     *
     * @param AdapterPluginManager $adapters
     */
    public static function setAdapterPluginManager(AdapterPluginManager $adapters)
    {
        static::$adapters = $adapters;
    }

    /**
     * Get the adapter plugin manager
     *
     * @return AdapterPluginManager
     */
    public static function getAdapterPluginManager()
    {
        if (static::$adapters === null) {
            static::$adapters = new AdapterPluginManager();
        }
        return static::$adapters;
    }

    /**
     * Set default BigInteger adapter
     *
     * @param string|Adapter\AdapterInterface $adapter
     */
    public static function setDefaultAdapter($adapter)
    {
        static::$defaultAdapter = static::factory($adapter);
    }

    /**
     * Get default BigInteger adapter
     *
     * @return null|Adapter\AdapterInterface
     */
    public static function getDefaultAdapter()
    {
        if (null === static::$defaultAdapter) {
            static::$defaultAdapter = static::getAvailableAdapter();
        }
        return static::$defaultAdapter;
    }

    /**
     * Determine and return available adapter
     *
     * @return Adapter\AdapterInterface
     * @throws Exception\RuntimeException
     */
    public static function getAvailableAdapter()
    {
        if (extension_loaded('gmp')) {
            $adapterName = 'Gmp';
        } elseif (extension_loaded('bcmath')) {
            $adapterName = 'Bcmath';
        } else {
            throw new Exception\RuntimeException('Big integer math support is not detected');
        }
        return static::factory($adapterName);
    }

    /**
     * Call adapter methods statically
     *
     * @param  string $method
     * @param  mixed $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $adapter = static::getDefaultAdapter();
        return call_user_func_array(array($adapter, $method), $args);
    }
}
