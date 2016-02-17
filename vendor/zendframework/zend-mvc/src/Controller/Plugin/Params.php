<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Controller\Plugin;

use Zend\Mvc\Exception\RuntimeException;
use Zend\Mvc\InjectApplicationEventInterface;

class Params extends AbstractPlugin
{
    /**
     * Grabs a param from route match by default.
     *
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public function __invoke($param = null, $default = null)
    {
        if ($param === null) {
            return $this;
        }
        return $this->fromRoute($param, $default);
    }

    /**
     * Return all files or a single file.
     *
     * @param  string $name File name to retrieve, or null to get all.
     * @param  mixed $default Default value to use when the file is missing.
     * @return array|\ArrayAccess|null
     */
    public function fromFiles($name = null, $default = null)
    {
        if ($name === null) {
            return $this->getController()->getRequest()->getFiles($name, $default)->toArray();
        }

        return $this->getController()->getRequest()->getFiles($name, $default);
    }

    /**
     * Return all header parameters or a single header parameter.
     *
     * @param  string $header Header name to retrieve, or null to get all.
     * @param  mixed $default Default value to use when the requested header is missing.
     * @return null|\Zend\Http\Header\HeaderInterface
     */
    public function fromHeader($header = null, $default = null)
    {
        if ($header === null) {
            return $this->getController()->getRequest()->getHeaders($header, $default)->toArray();
        }

        return $this->getController()->getRequest()->getHeaders($header, $default);
    }

    /**
     * Return all post parameters or a single post parameter.
     *
     * @param string $param Parameter name to retrieve, or null to get all.
     * @param mixed $default Default value to use when the parameter is missing.
     * @return mixed
     */
    public function fromPost($param = null, $default = null)
    {
        if ($param === null) {
            return $this->getController()->getRequest()->getPost($param, $default)->toArray();
        }

        return $this->getController()->getRequest()->getPost($param, $default);
    }

    /**
     * Return all query parameters or a single query parameter.
     *
     * @param string $param Parameter name to retrieve, or null to get all.
     * @param mixed $default Default value to use when the parameter is missing.
     * @return mixed
     */
    public function fromQuery($param = null, $default = null)
    {
        if ($param === null) {
            return $this->getController()->getRequest()->getQuery($param, $default)->toArray();
        }

        return $this->getController()->getRequest()->getQuery($param, $default);
    }

    /**
     * Return all route parameters or a single route parameter.
     *
     * @param string $param Parameter name to retrieve, or null to get all.
     * @param mixed $default Default value to use when the parameter is missing.
     * @return mixed
     * @throws RuntimeException
     */
    public function fromRoute($param = null, $default = null)
    {
        $controller = $this->getController();

        if (!$controller instanceof InjectApplicationEventInterface) {
            throw new RuntimeException(
                'Controllers must implement Zend\Mvc\InjectApplicationEventInterface to use this plugin.'
            );
        }

        if ($param === null) {
            return $controller->getEvent()->getRouteMatch()->getParams();
        }

        return $controller->getEvent()->getRouteMatch()->getParam($param, $default);
    }
}
