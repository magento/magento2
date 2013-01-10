<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_I18n
 */

namespace Zend\I18n\Translator;

use Zend\I18n\Exception;
use Zend\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for translation loaders.
 *
 * Enforces that loaders retrieved are either instances of
 * Loader\FileLoaderInterface or Loader\RemoteLoaderInterface. Additionally,
 * it registers a number of default loaders.
 *
 * @category   Zend
 * @package    Zend_I18n
 * @subpackage Translator
 */
class LoaderPluginManager extends AbstractPluginManager
{
    /**
     * Default set of loaders.
     *
     * @var array
     */
    protected $invokableClasses = array(
        'phparray' => 'Zend\I18n\Translator\Loader\PhpArray',
        'gettext'  => 'Zend\I18n\Translator\Loader\Gettext',
    );

    /**
     * Validate the plugin.
     *
     * Checks that the filter loaded is an instance of
     * Loader\FileLoaderInterface or Loader\RemoteLoaderInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Loader\FileLoaderInterface || $plugin instanceof Loader\RemoteLoaderInterface) {
            // we're okay
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Loader\FileLoaderInterface or %s\Loader\RemoteLoaderInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
