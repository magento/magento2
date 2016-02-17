<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\View\Http;

use ArrayAccess;
use Traversable;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager as ViewHelperManager;
use Zend\View\Renderer\PhpRenderer as ViewPhpRenderer;
use Zend\View\Resolver as ViewResolver;
use Zend\View\Strategy\PhpRendererStrategy;
use Zend\View\View;

/**
 * Prepares the view layer
 *
 * Instantiates and configures all classes related to the view layer, including
 * the renderer (and its associated resolver(s) and helper manager), the view
 * object (and its associated rendering strategies), and the various MVC
 * strategies and listeners.
 *
 * Defines and manages the following services:
 *
 * - ViewHelperManager (also aliased to Zend\View\HelperPluginManager)
 * - ViewTemplateMapResolver (also aliased to Zend\View\Resolver\TemplateMapResolver)
 * - ViewTemplatePathStack (also aliased to Zend\View\Resolver\TemplatePathStack)
 * - ViewResolver (also aliased to Zend\View\Resolver\AggregateResolver and ResolverInterface)
 * - ViewRenderer (also aliased to Zend\View\Renderer\PhpRenderer and RendererInterface)
 * - ViewPhpRendererStrategy (also aliased to Zend\View\Strategy\PhpRendererStrategy)
 * - View (also aliased to Zend\View\View)
 * - DefaultRenderingStrategy (also aliased to Zend\Mvc\View\Http\DefaultRenderingStrategy)
 * - ExceptionStrategy (also aliased to Zend\Mvc\View\Http\ExceptionStrategy)
 * - RouteNotFoundStrategy (also aliased to Zend\Mvc\View\Http\RouteNotFoundStrategy and 404Strategy)
 * - ViewModel
 */
class ViewManager extends AbstractListenerAggregate
{
    /**
     * @var object application configuration service
     */
    protected $config;

    /**
     * @var MvcEvent
     */
    protected $event;

    /**
     * @var ServiceManager
     */
    protected $services;

    /**@+
     * Various properties representing strategies and objects instantiated and
     * configured by the view manager
     */
    protected $exceptionStrategy;
    protected $helperManager;
    protected $mvcRenderingStrategy;
    protected $renderer;
    protected $rendererStrategy;
    protected $resolver;
    protected $routeNotFoundStrategy;
    protected $view;
    protected $viewModel;
    /**@-*/

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, array($this, 'onBootstrap'), 10000);
    }

    /**
     * Prepares the view layer
     *
     * @param  $event
     * @return void
     */
    public function onBootstrap($event)
    {
        $application  = $event->getApplication();
        $services     = $application->getServiceManager();
        $config       = $services->get('Config');
        $events       = $application->getEventManager();
        $sharedEvents = $events->getSharedManager();

        $this->config   = isset($config['view_manager']) && (is_array($config['view_manager']) || $config['view_manager'] instanceof ArrayAccess)
                        ? $config['view_manager']
                        : array();
        $this->services = $services;
        $this->event    = $event;

        $routeNotFoundStrategy   = $this->getRouteNotFoundStrategy();
        $exceptionStrategy       = $this->getExceptionStrategy();
        $mvcRenderingStrategy    = $this->getMvcRenderingStrategy();
        $injectTemplateListener  = $this->getInjectTemplateListener();
        $createViewModelListener = new CreateViewModelListener();
        $injectViewModelListener = new InjectViewModelListener();

        $this->registerMvcRenderingStrategies($events);
        $this->registerViewStrategies();

        $events->attach($routeNotFoundStrategy);
        $events->attach($exceptionStrategy);
        $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($injectViewModelListener, 'injectViewModel'), -100);
        $events->attach(MvcEvent::EVENT_RENDER_ERROR, array($injectViewModelListener, 'injectViewModel'), -100);
        $events->attach($mvcRenderingStrategy);

        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($createViewModelListener, 'createViewModelFromArray'), -80);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($routeNotFoundStrategy, 'prepareNotFoundViewModel'), -90);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($createViewModelListener, 'createViewModelFromNull'), -80);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($injectTemplateListener, 'injectTemplate'), -90);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($injectViewModelListener, 'injectViewModel'), -100);
    }

    /**
     * Instantiates and configures the renderer's helper manager
     *
     * @return ViewHelperManager
     */
    public function getHelperManager()
    {
        if ($this->helperManager) {
            return $this->helperManager;
        }

        return $this->helperManager = $this->services->get('ViewHelperManager');
    }

    /**
     * Instantiates and configures the renderer's resolver
     *
     * @return ViewResolver\ResolverInterface
     */
    public function getResolver()
    {
        if (null === $this->resolver) {
            $this->resolver = $this->services->get('ViewResolver');
        }

        return $this->resolver;
    }

    /**
     * Instantiates and configures the renderer
     *
     * @return ViewPhpRenderer
     */
    public function getRenderer()
    {
        if ($this->renderer) {
            return $this->renderer;
        }

        $this->renderer = new ViewPhpRenderer;
        $this->renderer->setHelperPluginManager($this->getHelperManager());
        $this->renderer->setResolver($this->getResolver());

        $model       = $this->getViewModel();
        $modelHelper = $this->renderer->plugin('view_model');
        $modelHelper->setRoot($model);

        $this->services->setService('ViewRenderer', $this->renderer);
        $this->services->setAlias('Zend\View\Renderer\PhpRenderer', 'ViewRenderer');
        $this->services->setAlias('Zend\View\Renderer\RendererInterface', 'ViewRenderer');

        return $this->renderer;
    }

    /**
     * Instantiates and configures the renderer strategy for the view
     *
     * @return PhpRendererStrategy
     */
    public function getRendererStrategy()
    {
        if ($this->rendererStrategy) {
            return $this->rendererStrategy;
        }

        $this->rendererStrategy = new PhpRendererStrategy(
            $this->getRenderer()
        );

        $this->services->setService('ViewPhpRendererStrategy', $this->rendererStrategy);
        $this->services->setAlias('Zend\View\Strategy\PhpRendererStrategy', 'ViewPhpRendererStrategy');

        return $this->rendererStrategy;
    }

    /**
     * Instantiates and configures the view
     *
     * @return View
     */
    public function getView()
    {
        if ($this->view) {
            return $this->view;
        }

        $this->view = new View();
        $this->view->setEventManager($this->services->get('EventManager'));
        $this->view->getEventManager()->attach($this->getRendererStrategy());

        $this->services->setService('View', $this->view);
        $this->services->setAlias('Zend\View\View', 'View');

        return $this->view;
    }

    /**
     * Retrieves the layout template name from the configuration
     *
     * @return string
     */
    public function getLayoutTemplate()
    {
        if (isset($this->config['layout'])) {
            return $this->config['layout'];
        }

        return 'layout/layout';
    }

    /**
     * Instantiates and configures the default MVC rendering strategy
     *
     * @return DefaultRenderingStrategy
     */
    public function getMvcRenderingStrategy()
    {
        if ($this->mvcRenderingStrategy) {
            return $this->mvcRenderingStrategy;
        }

        $this->mvcRenderingStrategy = new DefaultRenderingStrategy($this->getView());
        $this->mvcRenderingStrategy->setLayoutTemplate($this->getLayoutTemplate());

        $this->services->setService('DefaultRenderingStrategy', $this->mvcRenderingStrategy);
        $this->services->setAlias('Zend\Mvc\View\DefaultRenderingStrategy', 'DefaultRenderingStrategy');
        $this->services->setAlias('Zend\Mvc\View\Http\DefaultRenderingStrategy', 'DefaultRenderingStrategy');

        return $this->mvcRenderingStrategy;
    }

    /**
     * Instantiates and configures the exception strategy
     *
     * @return ExceptionStrategy
     */
    public function getExceptionStrategy()
    {
        if ($this->exceptionStrategy) {
            return $this->exceptionStrategy;
        }

        $this->exceptionStrategy = new ExceptionStrategy();

        $displayExceptions = false;
        $exceptionTemplate = 'error';

        if (isset($this->config['display_exceptions'])) {
            $displayExceptions = $this->config['display_exceptions'];
        }
        if (isset($this->config['exception_template'])) {
            $exceptionTemplate = $this->config['exception_template'];
        }

        $this->exceptionStrategy->setDisplayExceptions($displayExceptions);
        $this->exceptionStrategy->setExceptionTemplate($exceptionTemplate);

        $this->services->setService('ExceptionStrategy', $this->exceptionStrategy);
        $this->services->setAlias('Zend\Mvc\View\ExceptionStrategy', 'ExceptionStrategy');
        $this->services->setAlias('Zend\Mvc\View\Http\ExceptionStrategy', 'ExceptionStrategy');

        return $this->exceptionStrategy;
    }

    /**
     * Instantiates and configures the "route not found", or 404, strategy
     *
     * @return RouteNotFoundStrategy
     */
    public function getRouteNotFoundStrategy()
    {
        if ($this->routeNotFoundStrategy) {
            return $this->routeNotFoundStrategy;
        }

        $this->routeNotFoundStrategy = new RouteNotFoundStrategy();

        $displayExceptions     = false;
        $displayNotFoundReason = false;
        $notFoundTemplate      = '404';

        if (isset($this->config['display_exceptions'])) {
            $displayExceptions = $this->config['display_exceptions'];
        }
        if (isset($this->config['display_not_found_reason'])) {
            $displayNotFoundReason = $this->config['display_not_found_reason'];
        }
        if (isset($this->config['not_found_template'])) {
            $notFoundTemplate = $this->config['not_found_template'];
        }

        $this->routeNotFoundStrategy->setDisplayExceptions($displayExceptions);
        $this->routeNotFoundStrategy->setDisplayNotFoundReason($displayNotFoundReason);
        $this->routeNotFoundStrategy->setNotFoundTemplate($notFoundTemplate);

        $this->services->setService('RouteNotFoundStrategy', $this->routeNotFoundStrategy);
        $this->services->setAlias('Zend\Mvc\View\RouteNotFoundStrategy', 'RouteNotFoundStrategy');
        $this->services->setAlias('Zend\Mvc\View\Http\RouteNotFoundStrategy', 'RouteNotFoundStrategy');
        $this->services->setAlias('404Strategy', 'RouteNotFoundStrategy');

        return $this->routeNotFoundStrategy;
    }

    public function getInjectTemplateListener()
    {
        return $this->services->get('Zend\Mvc\View\Http\InjectTemplateListener');
    }

    /**
     * Configures the MvcEvent view model to ensure it has the template injected
     *
     * @return \Zend\View\Model\ModelInterface
     */
    public function getViewModel()
    {
        if ($this->viewModel) {
            return $this->viewModel;
        }

        $this->viewModel = $model = $this->event->getViewModel();
        $model->setTemplate($this->getLayoutTemplate());

        return $this->viewModel;
    }

    /**
     * Register additional mvc rendering strategies
     *
     * If there is a "mvc_strategies" key of the view manager configuration, loop
     * through it. Pull each as a service from the service manager, and, if it
     * is a ListenerAggregate, attach it to the view, at priority 100. This
     * latter allows each to trigger before the default mvc rendering strategy,
     * and for them to trigger in the order they are registered.
     *
     * @param EventManagerInterface $events
     * @return void
     */
    protected function registerMvcRenderingStrategies(EventManagerInterface $events)
    {
        if (!isset($this->config['mvc_strategies'])) {
            return;
        }
        $mvcStrategies = $this->config['mvc_strategies'];
        if (is_string($mvcStrategies)) {
            $mvcStrategies = array($mvcStrategies);
        }
        if (!is_array($mvcStrategies) && !$mvcStrategies instanceof Traversable) {
            return;
        }

        foreach ($mvcStrategies as $mvcStrategy) {
            if (!is_string($mvcStrategy)) {
                continue;
            }

            $listener = $this->services->get($mvcStrategy);
            if ($listener instanceof ListenerAggregateInterface) {
                $events->attach($listener, 100);
            }
        }
    }

    /**
     * Register additional view strategies
     *
     * If there is a "strategies" key of the view manager configuration, loop
     * through it. Pull each as a service from the service manager, and, if it
     * is a ListenerAggregate, attach it to the view, at priority 100. This
     * latter allows each to trigger before the default strategy, and for them
     * to trigger in the order they are registered.
     *
     * @return void
     */
    protected function registerViewStrategies()
    {
        if (!isset($this->config['strategies'])) {
            return;
        }
        $strategies = $this->config['strategies'];
        if (is_string($strategies)) {
            $strategies = array($strategies);
        }
        if (!is_array($strategies) && !$strategies instanceof Traversable) {
            return;
        }

        $view = $this->getView();

        foreach ($strategies as $strategy) {
            if (!is_string($strategy)) {
                continue;
            }

            $listener = $this->services->get($strategy);
            if ($listener instanceof ListenerAggregateInterface) {
                $view->getEventManager()->attach($listener, 100);
            }
        }
    }
}
