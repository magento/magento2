<?php
/**
 * Route to services available via REST API.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller\Rest\Router;

use Magento\Framework\App\RequestInterface as Request;
use Magento\Framework\App\RouterInterface;

class Route implements RouterInterface
{
    /**
     * @var string
     */
    protected $serviceClass;

    /**
     * @var string
     */
    protected $serviceMethod;

    /**
     * @var boolean
     */
    protected $secure;

    /**
     * @var array
     */
    protected $aclResources = [];

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var string
     */
    protected $route;

    /**
     * @param string $route
     */
    public function __construct($route = '')
    {
        $this->route = trim($route, '/');
    }

    /**
     * Split route by parts and variables
     *
     * @return array
     */
    protected function getRouteParts()
    {
        $result = [];
        $routeParts = explode('/', $this->route);
        foreach ($routeParts as $key => $value) {
            if ($this->isVariable($value)) {
                $this->variables[$key] = substr($value, 1);
                $value = null;
            }
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Check if current route part is a name of variable
     *
     * @param string $value
     * @return bool
     */
    protected function isVariable($value)
    {
        if (substr($value, 0, 1) == ':'
            && substr($value, 1, 1) != ':') {
            return true;
        }
        return false;
    }

    /**
     * Retrieve unified requested path
     *
     * @param string $path
     * @return array
     */
    protected function getPathParts($path)
    {
        return explode('/', trim($path, '/'));
    }

    /**
     * Check if current route matches the requested path
     *
     * @param Request $request
     * @return array|bool
     */
    public function match(Request $request)
    {
        /** @var \Magento\Framework\Webapi\Rest\Request $request */
        $pathParts = $this->getPathParts($request->getPathInfo());
        $routeParts = $this->getRouteParts();
        if (count($pathParts) <> count($routeParts)) {
            return false;
        }

        $result = [];
        foreach ($pathParts as $key => $value) {
            if (!array_key_exists($key, $routeParts)) {
                return false;
            }
            $variable = isset($this->variables[$key]) ? $this->variables[$key] : null;
            if ($variable) {
                $result[$variable] = urldecode($pathParts[$key]);
            } else {
                if ($value != $routeParts[$key]) {
                    return false;
                }
            }
        }
        return $result;
    }

    /**
     * Set service class.
     *
     * @param string $serviceClass
     * @return $this
     */
    public function setServiceClass($serviceClass)
    {
        $this->serviceClass = $serviceClass;
        return $this;
    }

    /**
     * Get service class.
     *
     * @return string
     */
    public function getServiceClass()
    {
        return $this->serviceClass;
    }

    /**
     * Set service method name.
     *
     * @param string $serviceMethod
     * @return $this
     */
    public function setServiceMethod($serviceMethod)
    {
        $this->serviceMethod = $serviceMethod;
        return $this;
    }

    /**
     * Get service method name.
     *
     * @return string
     */
    public function getServiceMethod()
    {
        return $this->serviceMethod;
    }

    /**
     * Set if the route is secure
     *
     * @param boolean $secure
     * @return $this
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
        return $this;
    }

    /**
     * Returns true if the route is secure
     *
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Set ACL resources list.
     *
     * @param array $aclResources
     * @return $this
     */
    public function setAclResources($aclResources)
    {
        $this->aclResources = $aclResources;
        return $this;
    }

    /**
     * Get ACL resources list.
     *
     * @return array
     */
    public function getAclResources()
    {
        return $this->aclResources;
    }

    /**
     * Set parameters list.
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * Get parameters list.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
