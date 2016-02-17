<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Serializer;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for serializer adapters.
 *
 * Enforces that adapters retrieved are instances of
 * Adapter\AdapterInterface. Additionally, it registers a number of default
 * adapters available.
 */
class AdapterPluginManager extends AbstractPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'igbinary'     => 'Zend\Serializer\Adapter\IgBinary',
        'json'         => 'Zend\Serializer\Adapter\Json',
        'msgpack'      => 'Zend\Serializer\Adapter\MsgPack',
        'phpcode'      => 'Zend\Serializer\Adapter\PhpCode',
        'phpserialize' => 'Zend\Serializer\Adapter\PhpSerialize',
        'pythonpickle' => 'Zend\Serializer\Adapter\PythonPickle',
        'wddx'         => 'Zend\Serializer\Adapter\Wddx',
    );

    /**
     * Validate the plugin
     *
     * Checks that the adapter loaded is an instance
     * of Adapter\AdapterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Adapter\AdapterInterface) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Adapter\AdapterInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
