<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Json
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Request.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Json
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Json_Server_Request
{
    /**
     * Request ID
     * @var mixed
     */
    protected $_id;

    /**
     * Flag
     * @var bool
     */
    protected $_isMethodError = false;

    /**
     * Requested method
     * @var string
     */
    protected $_method;

    /**
     * Regex for method
     * @var string
     */
    protected $_methodRegex = '/^[a-z][a-z0-9_.]*$/i';

    /**
     * Request parameters
     * @var array
     */
    protected $_params = array();

    /**
     * JSON-RPC version of request
     * @var string
     */
    protected $_version = '1.0';

    /**
     * Set request state
     *
     * @param  array $options
     * @return Zend_Json_Server_Request
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            } elseif ($key == 'jsonrpc') {
                $this->setVersion($value);
            }
        }
        return $this;
    }

    /**
     * Add a parameter to the request
     *
     * @param  mixed $value
     * @param  string $key
     * @return Zend_Json_Server_Request
     */
    public function addParam($value, $key = null)
    {
        if ((null === $key) || !is_string($key)) {
            $index = count($this->_params);
            $this->_params[$index] = $value;
        } else {
            $this->_params[$key] = $value;
        }

        return $this;
    }

    /**
     * Add many params
     *
     * @param  array $params
     * @return Zend_Json_Server_Request
     */
    public function addParams(array $params)
    {
        foreach ($params as $key => $value) {
            $this->addParam($value, $key);
        }
        return $this;
    }

    /**
     * Overwrite params
     *
     * @param  array $params
     * @return Zend_Json_Server_Request
     */
    public function setParams(array $params)
    {
        $this->_params = array();
        return $this->addParams($params);
    }

    /**
     * Retrieve param by index or key
     *
     * @param  int|string $index
     * @return mixed|null Null when not found
     */
    public function getParam($index)
    {
        if (array_key_exists($index, $this->_params)) {
            return $this->_params[$index];
        }

        return null;
    }

    /**
     * Retrieve parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Set request method
     *
     * @param  string $name
     * @return Zend_Json_Server_Request
     */
    public function setMethod($name)
    {
        if (!preg_match($this->_methodRegex, $name)) {
            $this->_isMethodError = true;
        } else {
            $this->_method = $name;
        }
        return $this;
    }

    /**
     * Get request method name
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Was a bad method provided?
     *
     * @return bool
     */
    public function isMethodError()
    {
        return $this->_isMethodError;
    }

    /**
     * Set request identifier
     *
     * @param  mixed $name
     * @return Zend_Json_Server_Request
     */
    public function setId($name)
    {
        $this->_id = (string) $name;
        return $this;
    }

    /**
     * Retrieve request identifier
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set JSON-RPC version
     *
     * @param  string $version
     * @return Zend_Json_Server_Request
     */
    public function setVersion($version)
    {
        if ('2.0' == $version) {
            $this->_version = '2.0';
        } else {
            $this->_version = '1.0';
        }
        return $this;
    }

    /**
     * Retrieve JSON-RPC version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Set request state based on JSON
     *
     * @param  string $json
     * @return void
     */
    public function loadJson($json)
    {
        #require_once 'Zend/Json.php';
        $options = Zend_Json::decode($json);
        $this->setOptions($options);
    }

    /**
     * Cast request to JSON
     *
     * @return string
     */
    public function toJson()
    {
        $jsonArray = array(
            'method' => $this->getMethod()
        );
        if (null !== ($id = $this->getId())) {
            $jsonArray['id'] = $id;
        }
        $params = $this->getParams();
        if (!empty($params)) {
            $jsonArray['params'] = $params;
        }
        if ('2.0' == $this->getVersion()) {
            $jsonArray['jsonrpc'] = '2.0';
        }

        #require_once 'Zend/Json.php';
        return Zend_Json::encode($jsonArray);
    }

    /**
     * Cast request to string (JSON)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
