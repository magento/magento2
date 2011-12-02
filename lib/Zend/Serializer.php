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
 * @package    Zend_Serializer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Serializer.php 20574 2010-01-24 17:39:14Z mabe $
 */

/** @see Zend_Loader_PluginLoader */
#require_once 'Zend/Loader/PluginLoader.php';

/**
 * @category   Zend
 * @package    Zend_Serializer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Serializer
{
    /**
     * Plugin loader to load adapter.
     *
     * @var null|Zend_Loader_PluginLoader
     */
    private static $_adapterLoader = null;

    /**
     * The default adapter.
     *
     * @var string|Zend_Serializer_AdapterInterface
     */
    protected static $_defaultAdapter = 'PhpSerialize';

    /**
     * Create a serializer adapter instance.
     *
     * @param string|Zend_Serializer_Adapter_AdapterInterface $adapterName Name of the adapter class
     * @param array |Zend_Config $opts Serializer options
     * @return Zend_Serializer_Adapter_AdapterInterface
     */
    public static function factory($adapterName, $opts = array()) 
    {
        if ($adapterName instanceof Zend_Serializer_Adapter_AdapterInterface) {
            return $adapterName; // $adapterName is already an adapter object
        }

        $adapterLoader = self::getAdapterLoader();
        try {
            $adapterClass = $adapterLoader->load($adapterName);
        } catch (Exception $e) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('Can\'t load serializer adapter "'.$adapterName.'"', 0, $e);
        }

        // ZF-8842:
        // check that the loaded class implements Zend_Serializer_Adapter_AdapterInterface without execute code
        if (!in_array('Zend_Serializer_Adapter_AdapterInterface', class_implements($adapterClass))) {
            #require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('The serializer adapter class "'.$adapterClass.'" must implement Zend_Serializer_Adapter_AdapterInterface');
        }

        return new $adapterClass($opts);
    }

    /**
     * Get the adapter plugin loader.
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function getAdapterLoader() 
    {
        if (self::$_adapterLoader === null) {
            self::$_adapterLoader = self::_getDefaultAdapterLoader();
        }
        return self::$_adapterLoader;
    }

    /**
     * Change the adapter plugin load.
     *
     * @param  Zend_Loader_PluginLoader $pluginLoader
     * @return void
     */
    public static function setAdapterLoader(Zend_Loader_PluginLoader $pluginLoader) 
    {
        self::$_adapterLoader = $pluginLoader;
    }
    
    /**
     * Resets the internal adapter plugin loader
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function resetAdapterLoader()
    {
        self::$_adapterLoader = self::_getDefaultAdapterLoader();
        return self::$_adapterLoader;
    }
    
    /**
     * Returns a default adapter plugin loader
     *
     * @return Zend_Loader_PluginLoader
     */
    protected static function _getDefaultAdapterLoader()
    {
        $loader = new Zend_Loader_PluginLoader();
        $loader->addPrefixPath('Zend_Serializer_Adapter', dirname(__FILE__).'/Serializer/Adapter');
        return $loader;
    }

    /**
     * Change the default adapter.
     *
     * @param string|Zend_Serializer_Adapter_AdapterInterface $adapter
     * @param array|Zend_Config $options
     */
    public static function setDefaultAdapter($adapter, $options = array()) 
    {
        self::$_defaultAdapter = self::factory($adapter, $options);
    }

    /**
     * Get the default adapter.
     *
     * @return Zend_Serializer_Adapter_AdapterInterface
     */
    public static function getDefaultAdapter() 
    {
        if (!self::$_defaultAdapter instanceof Zend_Serializer_Adapter_AdapterInterface) {
            self::setDefaultAdapter(self::$_defaultAdapter);
        }
        return self::$_defaultAdapter;
    }

    /**
     * Generates a storable representation of a value using the default adapter.
     *
     * @param mixed $value
     * @param array $options
     * @return string
     * @throws Zend_Serializer_Exception
     */
    public static function serialize($value, array $options = array()) 
    {
        if (isset($options['adapter'])) {
            $adapter = self::factory($options['adapter']);
            unset($options['adapter']);
        } else {
            $adapter = self::getDefaultAdapter();
        }

        return $adapter->serialize($value, $options);
    }

    /**
     * Creates a PHP value from a stored representation using the default adapter.
     *
     * @param string $serialized
     * @param array $options
     * @return mixed
     * @throws Zend_Serializer_Exception
     */
    public static function unserialize($serialized, array $options = array()) 
    {
        if (isset($options['adapter'])) {
            $adapter = self::factory($options['adapter']);
            unset($options['adapter']);
        } else {
            $adapter = self::getDefaultAdapter();
        }

        return $adapter->unserialize($serialized, $options);
    }
}
