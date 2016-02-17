<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View;

use ArrayAccess;
use Zend\EventManager\Event;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\View\Model\ModelInterface as Model;
use Zend\View\Renderer\RendererInterface as Renderer;

class ViewEvent extends Event
{
    /**#@+
     * View events triggered by eventmanager
     */
    const EVENT_RENDERER = 'renderer';
    const EVENT_RENDERER_POST = 'renderer.post';
    const EVENT_RESPONSE = 'response';
    /**#@-*/

    /**
     * @var null|Model
     */
    protected $model;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var null|Request
     */
    protected $request;

    /**
     * @var null|Response
     */
    protected $response;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * Set the view model
     *
     * @param  Model $model
     * @return ViewEvent
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set the MVC request object
     *
     * @param  Request $request
     * @return ViewEvent
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Set the MVC response object
     *
     * @param  Response $response
     * @return ViewEvent
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Set result of rendering
     *
     * @param  mixed $result
     * @return ViewEvent
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * Retrieve the view model
     *
     * @return null|Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set value for renderer
     *
     * @param  Renderer $renderer
     * @return ViewEvent
     */
    public function setRenderer(Renderer $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Get value for renderer
     *
     * @return null|Renderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Retrieve the MVC request object
     *
     * @return null|Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Retrieve the MVC response object
     *
     * @return null|Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Retrieve the result of rendering
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Get event parameter
     *
     * @param  string $name
     * @param  mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        switch ($name) {
            case 'model':
                return $this->getModel();
            case 'renderer':
                return $this->getRenderer();
            case 'request':
                return $this->getRequest();
            case 'response':
                return $this->getResponse();
            case 'result':
                return $this->getResult();
            default:
                return parent::getParam($name, $default);
        }
    }

    /**
     * Get all event parameters
     *
     * @return array|\ArrayAccess
     */
    public function getParams()
    {
        $params             = parent::getParams();
        $params['model']    = $this->getModel();
        $params['renderer'] = $this->getRenderer();
        $params['request']  = $this->getRequest();
        $params['response'] = $this->getResponse();
        $params['result']   = $this->getResult();
        return $params;
    }

    /**
     * Set event parameters
     *
     * @param  array|object|ArrayAccess $params
     * @return ViewEvent
     */
    public function setParams($params)
    {
        parent::setParams($params);
        if (!is_array($params) && !$params instanceof ArrayAccess) {
            return $this;
        }

        foreach (array('model', 'renderer', 'request', 'response', 'result') as $param) {
            if (isset($params[$param])) {
                $method = 'set' . $param;
                $this->$method($params[$param]);
            }
        }
        return $this;
    }

    /**
     * Set an individual event parameter
     *
     * @param  string $name
     * @param  mixed $value
     * @return ViewEvent
     */
    public function setParam($name, $value)
    {
        switch ($name) {
            case 'model':
                $this->setModel($value);
                break;
            case 'renderer':
                $this->setRenderer($value);
                break;
            case 'request':
                $this->setRequest($value);
                break;
            case 'response':
                $this->setResponse($value);
                break;
            case 'result':
                $this->setResult($value);
                break;
            default:
                parent::setParam($name, $value);
                break;
        }
        return $this;
    }
}
