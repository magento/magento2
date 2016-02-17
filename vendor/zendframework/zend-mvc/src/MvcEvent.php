<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc;

use Zend\EventManager\Event;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ModelInterface as Model;
use Zend\View\Model\ViewModel;

class MvcEvent extends Event
{
    /**#@+
     * Mvc events triggered by eventmanager
     */
    const EVENT_BOOTSTRAP      = 'bootstrap';
    const EVENT_DISPATCH       = 'dispatch';
    const EVENT_DISPATCH_ERROR = 'dispatch.error';
    const EVENT_FINISH         = 'finish';
    const EVENT_RENDER         = 'render';
    const EVENT_RENDER_ERROR   = 'render.error';
    const EVENT_ROUTE          = 'route';
    /**#@-*/

    protected $application;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * @var Router\RouteStackInterface
     */
    protected $router;

    /**
     * @var Router\RouteMatch
     */
    protected $routeMatch;

    /**
     * @var Model
     */
    protected $viewModel;

    /**
     * Set application instance
     *
     * @param  ApplicationInterface $application
     * @return MvcEvent
     */
    public function setApplication(ApplicationInterface $application)
    {
        $this->setParam('application', $application);
        $this->application = $application;
        return $this;
    }

    /**
     * Get application instance
     *
     * @return ApplicationInterface
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Get router
     *
     * @return Router\RouteStackInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Set router
     *
     * @param Router\RouteStackInterface $router
     * @return MvcEvent
     */
    public function setRouter(Router\RouteStackInterface $router)
    {
        $this->setParam('router', $router);
        $this->router = $router;
        return $this;
    }

    /**
     * Get route match
     *
     * @return Router\RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }

    /**
     * Set route match
     *
     * @param Router\RouteMatch $matches
     * @return MvcEvent
     */
    public function setRouteMatch(Router\RouteMatch $matches)
    {
        $this->setParam('route-match', $matches);
        $this->routeMatch = $matches;
        return $this;
    }

    /**
     * Get request
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set request
     *
     * @param Request $request
     * @return MvcEvent
     */
    public function setRequest(Request $request)
    {
        $this->setParam('request', $request);
        $this->request = $request;
        return $this;
    }

    /**
     * Get response
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set response
     *
     * @param Response $response
     * @return MvcEvent
     */
    public function setResponse(Response $response)
    {
        $this->setParam('response', $response);
        $this->response = $response;
        return $this;
    }

    /**
     * Set the view model
     *
     * @param  Model $viewModel
     * @return MvcEvent
     */
    public function setViewModel(Model $viewModel)
    {
        $this->viewModel = $viewModel;
        return $this;
    }

    /**
     * Get the view model
     *
     * @return Model
     */
    public function getViewModel()
    {
        if (null === $this->viewModel) {
            $this->setViewModel(new ViewModel());
        }
        return $this->viewModel;
    }

    /**
     * Get result
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set result
     *
     * @param mixed $result
     * @return MvcEvent
     */
    public function setResult($result)
    {
        $this->setParam('__RESULT__', $result);
        $this->result = $result;
        return $this;
    }

    /**
     * Does the event represent an error response?
     *
     * @return bool
     */
    public function isError()
    {
        return (bool) $this->getParam('error', false);
    }

    /**
     * Set the error message (indicating error in handling request)
     *
     * @param  string $message
     * @return MvcEvent
     */
    public function setError($message)
    {
        $this->setParam('error', $message);
        return $this;
    }

    /**
     * Retrieve the error message, if any
     *
     * @return string
     */
    public function getError()
    {
        return $this->getParam('error', '');
    }

    /**
     * Get the currently registered controller name
     *
     * @return string
     */
    public function getController()
    {
        return $this->getParam('controller');
    }

    /**
     * Set controller name
     *
     * @param  string $name
     * @return MvcEvent
     */
    public function setController($name)
    {
        $this->setParam('controller', $name);
        return $this;
    }

    /**
     * Get controller class
     *
     * @return string
     */
    public function getControllerClass()
    {
        return $this->getParam('controller-class');
    }

    /**
     * Set controller class
     *
     * @param string $class
     * @return MvcEvent
     */
    public function setControllerClass($class)
    {
        $this->setParam('controller-class', $class);
        return $this;
    }
}
