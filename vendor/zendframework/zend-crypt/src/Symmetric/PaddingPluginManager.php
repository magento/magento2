<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt\Symmetric;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for the padding adapter instances.
 *
 * Enforces that padding adapters retrieved are instances of
 * Padding\PaddingInterface. Additionally, it registers a number of default
 * padding adapters available.
 */
class PaddingPluginManager extends AbstractPluginManager
{
    /**
     * Default set of padding adapters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'pkcs7' => 'Zend\Crypt\Symmetric\Padding\Pkcs7'
    );

    /**
     * Do not share by default
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the padding adapter loaded is an instance of Padding\PaddingInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Padding\PaddingInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Padding\PaddingInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
