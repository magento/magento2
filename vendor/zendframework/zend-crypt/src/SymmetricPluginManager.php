<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Crypt;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for the symmetric adapter instances.
 *
 * Enforces that symmetric adapters retrieved are instances of
 * Symmetric\SymmetricInterface. Additionally, it registers a number of default
 * symmetric adapters available.
 */
class SymmetricPluginManager extends AbstractPluginManager
{
    /**
     * Default set of symmetric adapters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'mcrypt' => 'Zend\Crypt\Symmetric\Mcrypt',
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
     * Checks that the adapter loaded is an instance
     * of Symmetric\SymmetricInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Symmetric\SymmetricInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Symmetric\SymmetricInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
