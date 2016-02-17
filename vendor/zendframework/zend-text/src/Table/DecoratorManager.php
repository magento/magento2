<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Text\Table;

use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for text table decorators
 *
 * Enforces that decorators retrieved are instances of
 * Decorator\DecoratorInterface. Additionally, it registers a number of default
 * decorators.
 */
class DecoratorManager extends AbstractPluginManager
{
    /**
     * Default set of decorators
     *
     * @var array
     */
    protected $invokableClasses = array(
        'ascii'   => 'Zend\Text\Table\Decorator\Ascii',
        'blank'   => 'Zend\Text\Table\Decorator\Blank',
        'unicode' => 'Zend\Text\Table\Decorator\Unicode',
    );

    /**
     * Validate the plugin
     *
     * Checks that the decorator loaded is an instance of Decorator\DecoratorInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidDecoratorException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Decorator\DecoratorInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidDecoratorException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Decorator\DecoratorInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
