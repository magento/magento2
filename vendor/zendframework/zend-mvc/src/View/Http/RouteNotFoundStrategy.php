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
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ViewModel;

class RouteNotFoundStrategy extends AbstractListenerAggregate
{
    /**
     * Whether or not to display exceptions related to the 404 condition
     *
     * @var bool
     */
    protected $displayExceptions = false;

    /**
     * Whether or not to display the reason for a 404
     *
     * @var bool
     */
    protected $displayNotFoundReason = false;

    /**
     * Template to use to report page not found conditions
     *
     * @var string
     */
    protected $notFoundTemplate = 'error';

    /**
     * The reason for a not-found condition
     *
     * @var false|string
     */
    protected $reason = false;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, array($this, 'prepareNotFoundViewModel'), -90);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'detectNotFoundError'));
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'prepareNotFoundViewModel'));
    }

    /**
     * Set value indicating whether or not to display exceptions related to a not-found condition
     *
     * @param  bool $displayExceptions
     * @return RouteNotFoundStrategy
     */
    public function setDisplayExceptions($displayExceptions)
    {
        $this->displayExceptions = (bool) $displayExceptions;
        return $this;
    }

    /**
     * Should we display exceptions related to a not-found condition?
     *
     * @return bool
     */
    public function displayExceptions()
    {
        return $this->displayExceptions;
    }

    /**
     * Set value indicating whether or not to display the reason for a not-found condition
     *
     * @param  bool $displayNotFoundReason
     * @return RouteNotFoundStrategy
     */
    public function setDisplayNotFoundReason($displayNotFoundReason)
    {
        $this->displayNotFoundReason = (bool) $displayNotFoundReason;
        return $this;
    }

    /**
     * Should we display the reason for a not-found condition?
     *
     * @return bool
     */
    public function displayNotFoundReason()
    {
        return $this->displayNotFoundReason;
    }

    /**
     * Get template for not found conditions
     *
     * @param  string $notFoundTemplate
     * @return RouteNotFoundStrategy
     */
    public function setNotFoundTemplate($notFoundTemplate)
    {
        $this->notFoundTemplate = (string) $notFoundTemplate;
        return $this;
    }

    /**
     * Get template for not found conditions
     *
     * @return string
     */
    public function getNotFoundTemplate()
    {
        return $this->notFoundTemplate;
    }

    /**
     * Detect if an error is a 404 condition
     *
     * If a "controller not found" or "invalid controller" error type is
     * encountered, sets the response status code to 404.
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function detectNotFoundError(MvcEvent $e)
    {
        $error = $e->getError();
        if (empty($error)) {
            return;
        }

        switch ($error) {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:
            case Application::ERROR_ROUTER_NO_MATCH:
                $this->reason = $error;
                $response = $e->getResponse();
                if (!$response) {
                    $response = new HttpResponse();
                    $e->setResponse($response);
                }
                $response->setStatusCode(404);
                break;
            default:
                return;
        }
    }

    /**
     * Create and return a 404 view model
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareNotFoundViewModel(MvcEvent $e)
    {
        $vars = $e->getResult();
        if ($vars instanceof Response) {
            // Already have a response as the result
            return;
        }

        $response = $e->getResponse();
        if ($response->getStatusCode() != 404) {
            // Only handle 404 responses
            return;
        }

        if (!$vars instanceof ViewModel) {
            $model = new ViewModel();
            if (is_string($vars)) {
                $model->setVariable('message', $vars);
            } else {
                $model->setVariable('message', 'Page not found.');
            }
        } else {
            $model = $vars;
            if ($model->getVariable('message') === null) {
                $model->setVariable('message', 'Page not found.');
            }
        }

        $model->setTemplate($this->getNotFoundTemplate());

        // If displaying reasons, inject the reason
        $this->injectNotFoundReason($model);

        // If displaying exceptions, inject
        $this->injectException($model, $e);

        // Inject controller if we're displaying either the reason or the exception
        $this->injectController($model, $e);

        $e->setResult($model);
    }

    /**
     * Inject the not-found reason into the model
     *
     * If $displayNotFoundReason is enabled, checks to see if $reason is set,
     * and, if so, injects it into the model. If not, it injects
     * Application::ERROR_CONTROLLER_CANNOT_DISPATCH.
     *
     * @param  ViewModel $model
     * @return void
     */
    protected function injectNotFoundReason(ViewModel $model)
    {
        if (!$this->displayNotFoundReason()) {
            return;
        }

        // no route match, controller not found, or controller invalid
        if ($this->reason) {
            $model->setVariable('reason', $this->reason);
            return;
        }

        // otherwise, must be a case of the controller not being able to
        // dispatch itself.
        $model->setVariable('reason', Application::ERROR_CONTROLLER_CANNOT_DISPATCH);
    }

    /**
     * Inject the exception message into the model
     *
     * If $displayExceptions is enabled, and an exception is found in the
     * event, inject it into the model.
     *
     * @param  ViewModel $model
     * @param  MvcEvent $e
     * @return void
     */
    protected function injectException($model, $e)
    {
        if (!$this->displayExceptions()) {
            return;
        }

        $model->setVariable('display_exceptions', true);

        $exception = $e->getParam('exception', false);
        if (!$exception instanceof \Exception) {
            return;
        }

        $model->setVariable('exception', $exception);
    }

    /**
     * Inject the controller and controller class into the model
     *
     * If either $displayExceptions or $displayNotFoundReason are enabled,
     * injects the controllerClass from the MvcEvent. It checks to see if a
     * controller is present in the MvcEvent, and, if not, grabs it from
     * the route match if present; if a controller is found, it injects it into
     * the model.
     *
     * @param  ViewModel $model
     * @param  MvcEvent $e
     * @return void
     */
    protected function injectController($model, $e)
    {
        if (!$this->displayExceptions() && !$this->displayNotFoundReason()) {
            return;
        }

        $controller = $e->getController();
        if (empty($controller)) {
            $routeMatch = $e->getRouteMatch();
            if (empty($routeMatch)) {
                return;
            }

            $controller = $routeMatch->getParam('controller', false);
            if (!$controller) {
                return;
            }
        }

        $controllerClass = $e->getControllerClass();
        $model->setVariable('controller', $controller);
        $model->setVariable('controller_class', $controllerClass);
    }
}
