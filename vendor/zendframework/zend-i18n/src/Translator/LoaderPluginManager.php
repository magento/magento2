<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
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
 * If you are wanting to use the ability to load translation files from the
 * include_path, you will need to create a factory to override the defaults
 * defined in this class. A simple factory might look like:
 *
 * <code>
 * function ($translators) {
 *     $adapter = new Gettext();
 *     $adapter->setUseIncludePath(true);
 *     return $adapter;
 * }
 * </code>
 *
 * You may need to override the Translator service factory to make this happen
 * more easily. That can be done by extending it:
 *
 * <code>
 * use Zend\I18n\Translator\TranslatorServiceFactory;
 * // or Zend\Mvc\I18n\TranslatorServiceFactory
 * use Zend\ServiceManager\ServiceLocatorInterface;
 *
 * class MyTranslatorServiceFactory extends TranslatorServiceFactory
 * {
 *     public function createService(ServiceLocatorInterface $services)
 *     {
 *         $translator = parent::createService($services);
 *         $translator->getLoaderPluginManager()->setFactory(...);
 *         return $translator;
 *     }
 * }
 * </code>
 *
 * You would then specify your custom factory in your service configuration.
 */
class LoaderPluginManager extends AbstractPluginManager
{
    /**
     * Default set of loaders.
     *
     * @var array
     */
    protected $invokableClasses = array(
        'gettext'  => 'Zend\I18n\Translator\Loader\Gettext',
        'ini'      => 'Zend\I18n\Translator\Loader\Ini',
        'phparray' => 'Zend\I18n\Translator\Loader\PhpArray',
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
