<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Controller;

use Zend\Mvc\Exception;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\Stdlib\DispatchableInterface;

/**
 * Plugin manager implementation for controllers
 *
 * Registers a number of default plugins, and contains an initializer for
 * injecting plugins with the current controller.
 */
class PluginManager extends AbstractPluginManager
{
    /**
     * Default set of plugins factories
     *
     * @var array
     */
    protected $factories = array(
        'forward'  => 'Zend\Mvc\Controller\Plugin\Service\ForwardFactory',
        'identity' => 'Zend\Mvc\Controller\Plugin\Service\IdentityFactory',
    );

    /**
     * Default set of plugins
     *
     * @var array
     */
    protected $invokableClasses = array(
        'acceptableviewmodelselector' => 'Zend\Mvc\Controller\Plugin\AcceptableViewModelSelector',
        'filepostredirectget'         => 'Zend\Mvc\Controller\Plugin\FilePostRedirectGet',
        'flashmessenger'              => 'Zend\Mvc\Controller\Plugin\FlashMessenger',
        'layout'                      => 'Zend\Mvc\Controller\Plugin\Layout',
        'params'                      => 'Zend\Mvc\Controller\Plugin\Params',
        'postredirectget'             => 'Zend\Mvc\Controller\Plugin\PostRedirectGet',
        'redirect'                    => 'Zend\Mvc\Controller\Plugin\Redirect',
        'url'                         => 'Zend\Mvc\Controller\Plugin\Url',
        'createhttpnotfoundmodel'     => 'Zend\Mvc\Controller\Plugin\CreateHttpNotFoundModel',
        'createconsolenotfoundmodel'  => 'Zend\Mvc\Controller\Plugin\CreateConsoleNotFoundModel',
    );

    /**
     * Default set of plugin aliases
     *
     * @var array
     */
    protected $aliases = array(
        'prg'     => 'postredirectget',
        'fileprg' => 'filepostredirectget',
    );

    /**
     * @var DispatchableInterface
     */
    protected $controller;

    /**
     * Retrieve a registered instance
     *
     * After the plugin is retrieved from the service locator, inject the
     * controller in the plugin every time it is requested. This is required
     * because a controller can use a plugin and another controller can be
     * dispatched afterwards. If this second controller uses the same plugin
     * as the first controller, the reference to the controller inside the
     * plugin is lost.
     *
     * @param  string $name
     * @param  mixed  $options
     * @param  bool   $usePeeringServiceManagers
     * @return mixed
     */
    public function get($name, $options = array(), $usePeeringServiceManagers = true)
    {
        $plugin = parent::get($name, $options, $usePeeringServiceManagers);
        $this->injectController($plugin);

        return $plugin;
    }

    /**
     * Set controller
     *
     * @param  DispatchableInterface $controller
     * @return PluginManager
     */
    public function setController(DispatchableInterface $controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * Retrieve controller instance
     *
     * @return null|DispatchableInterface
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Inject a helper instance with the registered controller
     *
     * @param  object $plugin
     * @return void
     */
    public function injectController($plugin)
    {
        if (!is_object($plugin)) {
            return;
        }
        if (!method_exists($plugin, 'setController')) {
            return;
        }

        $controller = $this->getController();
        if (!$controller instanceof DispatchableInterface) {
            return;
        }

        $plugin->setController($controller);
    }

    /**
     * Validate the plugin
     *
     * Any plugin is considered valid in this context.
     *
     * @param  mixed                            $plugin
     * @return void
     * @throws Exception\InvalidPluginException
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Plugin\PluginInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidPluginException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Plugin\PluginInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
