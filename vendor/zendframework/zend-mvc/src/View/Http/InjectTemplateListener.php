<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\View\Http;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface as Events;
use Zend\Filter\Word\CamelCaseToDash as CamelCaseToDashFilter;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\View\Model\ModelInterface as ViewModel;

class InjectTemplateListener extends AbstractListenerAggregate
{
    /**
     * FilterInterface/inflector used to normalize names for use as template identifiers
     *
     * @var mixed
     */
    protected $inflector;

    /**
     * Array of controller namespace -> template mappings
     *
     * @var array
     */
    protected $controllerMap = array();

    /**
     * Flag to force the use of the route match controller param
     *
     * @var boolean
     */
    protected $preferRouteMatchController = false;

    /**
     * {@inheritDoc}
     */
    public function attach(Events $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'injectTemplate'), -90);
    }

    /**
     * Inject a template into the view model, if none present
     *
     * Template is derived from the controller found in the route match, and,
     * optionally, the action, if present.
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function injectTemplate(MvcEvent $e)
    {
        $model = $e->getResult();
        if (!$model instanceof ViewModel) {
            return;
        }

        $template = $model->getTemplate();
        if (!empty($template)) {
            return;
        }

        $routeMatch = $e->getRouteMatch();
        $controller = $e->getTarget();
        if (is_object($controller)) {
            $controller = get_class($controller);
        }

        $routeMatchController = $routeMatch->getParam('controller', '');
        if (!$controller || ($this->preferRouteMatchController && $routeMatchController)) {
            $controller = $routeMatchController;
        }

        $template = $this->mapController($controller);
        if (!$template) {
            $module     = $this->deriveModuleNamespace($controller);

            if ($namespace = $routeMatch->getParam(ModuleRouteListener::MODULE_NAMESPACE)) {
                $controllerSubNs = $this->deriveControllerSubNamespace($namespace);
                if (!empty($controllerSubNs)) {
                    if (!empty($module)) {
                        $module .= '/' . $controllerSubNs;
                    } else {
                        $module = $controllerSubNs;
                    }
                }
            }

            $controller = $this->deriveControllerClass($controller);
            $template   = $this->inflectName($module);

            if (!empty($template)) {
                $template .= '/';
            }
            $template  .= $this->inflectName($controller);
        }

        $action     = $routeMatch->getParam('action');
        if (null !== $action) {
            $template .= '/' . $this->inflectName($action);
        }
        $model->setTemplate($template);
    }

    /**
     * Set map of controller namespace -> template pairs
     *
     * @param  array $map
     * @return self
     */
    public function setControllerMap(array $map)
    {
        krsort($map);
        $this->controllerMap = $map;
        return $this;
    }

    /**
     * Maps controller to template if controller namespace is whitelisted or mapped
     *
     * @param string $controller controller FQCN
     * @return string|false template name or false if controller was not matched
     */
    public function mapController($controller)
    {
        if (! is_string($controller)) {
            return false;
        }

        foreach ($this->controllerMap as $namespace => $replacement) {
            if (
                // Allow disabling rule by setting value to false since config
                // merging have no feature to remove entries
                false == $replacement
                // Match full class or full namespace
                || !($controller === $namespace || strpos($controller, $namespace . '\\') === 0)
            ) {
                continue;
            }

            $map = '';
            // Map namespace to $replacement if its value is string
            if (is_string($replacement)) {
                $map = rtrim($replacement, '/') . '/';
                $controller = substr($controller, strlen($namespace) + 1) ?: '';
            }

            //strip Controller namespace(s) (but not classname)
            $parts = explode('\\', $controller);
            array_pop($parts);
            $parts = array_diff($parts, array('Controller'));
            //strip trailing Controller in class name
            $parts[] = $this->deriveControllerClass($controller);
            $controller = implode('/', $parts);

            $template = trim($map . $controller, '/');

            //inflect CamelCase to dash
            return $this->inflectName($template);
        }
        return false;
    }

    /**
     * Inflect a name to a normalized value
     *
     * @param  string $name
     * @return string
     */
    protected function inflectName($name)
    {
        if (!$this->inflector) {
            $this->inflector = new CamelCaseToDashFilter();
        }
        $name = $this->inflector->filter($name);
        return strtolower($name);
    }

    /**
     * Determine the top-level namespace of the controller
     *
     * @param  string $controller
     * @return string
     */
    protected function deriveModuleNamespace($controller)
    {
        if (!strstr($controller, '\\')) {
            return '';
        }
        $module = substr($controller, 0, strpos($controller, '\\'));
        return $module;
    }

    /**
     * @param $namespace
     * @return string
     */
    protected function deriveControllerSubNamespace($namespace)
    {
        if (!strstr($namespace, '\\')) {
            return '';
        }
        $nsArray = explode('\\', $namespace);

        // Remove the first two elements representing the module and controller directory.
        $subNsArray = array_slice($nsArray, 2);
        if (empty($subNsArray)) {
            return '';
        }
        return implode('/', $subNsArray);
    }

    /**
     * Determine the name of the controller
     *
     * Strip the namespace, and the suffix "Controller" if present.
     *
     * @param  string $controller
     * @return string
     */
    protected function deriveControllerClass($controller)
    {
        if (strstr($controller, '\\')) {
            $controller = substr($controller, strrpos($controller, '\\') + 1);
        }

        if ((10 < strlen($controller))
            && ('Controller' == substr($controller, -10))
        ) {
            $controller = substr($controller, 0, -10);
        }

        return $controller;
    }

    /**
     * Sets the flag to instruct the listener to prefer the route match controller param
     * over the class name
     *
     * @param boolean $preferRouteMatchController
     */
    public function setPreferRouteMatchController($preferRouteMatchController)
    {
        $this->preferRouteMatchController = (bool) $preferRouteMatchController;
    }

    /**
     * @return boolean
     */
    public function isPreferRouteMatchController()
    {
        return $this->preferRouteMatchController;
    }
}
