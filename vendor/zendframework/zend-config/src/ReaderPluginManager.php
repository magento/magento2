<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config;

use Zend\ServiceManager\AbstractPluginManager;

class ReaderPluginManager extends AbstractPluginManager
{
    /**
     * Default set of readers
     *
     * @var array
     */
    protected $invokableClasses = array(
        'ini'             => 'Zend\Config\Reader\Ini',
        'json'            => 'Zend\Config\Reader\Json',
        'xml'             => 'Zend\Config\Reader\Xml',
        'yaml'            => 'Zend\Config\Reader\Yaml',
        'javaproperties'  => 'Zend\Config\Reader\JavaProperties',
    );

    /**
     * Validate the plugin
     * Checks that the reader loaded is an instance of Reader\ReaderInterface.
     *
     * @param  Reader\ReaderInterface $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Reader\ReaderInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Reader\ReaderInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
