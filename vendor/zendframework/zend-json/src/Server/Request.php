<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Json\Server;

use Zend\Json;

/**
 * @todo       Revised method regex to allow NS; however, should SMD be revised to strip PHP NS instead when attaching functions?
 */
class Request
{
    /**
     * Request ID
     * @var mixed
     */
    protected $id;

    /**
     * Flag
     * @var bool
     */
    protected $isMethodError = false;

    /**
     * Flag
     * @var bool
     */
    protected $isParseError = false;

    /**
     * Requested method
     * @var string
     */
    protected $method;

    /**
     * Regex for method
     * @var string
     */
    protected $methodRegex = '/^[a-z][a-z0-9\\\\_.]*$/i';

    /**
     * Request parameters
     * @var array
     */
    protected $params = array();

    /**
     * JSON-RPC version of request
     * @var string
     */
    protected $version = '1.0';

    /**
     * Set request state
     *
     * @param  array $options
     * @return \Zend\Json\Server\Request
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
     * @return \Zend\Json\Server\Request
     */
    public function addParam($value, $key = null)
    {
        if ((null === $key) || !is_string($key)) {
            $index = count($this->params);
            $this->params[$index] = $value;
        } else {
            $this->params[$key] = $value;
        }

        return $this;
    }

    /**
     * Add many params
     *
     * @param  array $params
     * @return \Zend\Json\Server\Request
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
     * @return \Zend\Json\Server\Request
     */
    public function setParams(array $params)
    {
        $this->params = array();
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
        if (array_key_exists($index, $this->params)) {
            return $this->params[$index];
        }

        return;
    }

    /**
     * Retrieve parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set request method
     *
     * @param  string $name
     * @return \Zend\Json\Server\Request
     */
    public function setMethod($name)
    {
        if (!preg_match($this->methodRegex, $name)) {
            $this->isMethodError = true;
        } else {
            $this->method = $name;
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
        return $this->method;
    }

    /**
     * Was a bad method provided?
     *
     * @return bool
     */
    public function isMethodError()
    {
        return $this->isMethodError;
    }

    /**
     * Was a malformed JSON provided?
     *
     * @return bool
     */
    public function isParseError()
    {
        return $this->isParseError;
    }

    /**
     * Set request identifier
     *
     * @param  mixed $name
     * @return \Zend\Json\Server\Request
     */
    public function setId($name)
    {
        $this->id = (string) $name;
        return $this;
    }

    /**
     * Retrieve request identifier
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set JSON-RPC version
     *
     * @param  string $version
     * @return \Zend\Json\Server\Request
     */
    public function setVersion($version)
    {
        if ('2.0' == $version) {
            $this->version = '2.0';
        } else {
            $this->version = '1.0';
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
        return $this->version;
    }

    /**
     * Set request state based on JSON
     *
     * @param  string $json
     * @return void
     */
    public function loadJson($json)
    {
        try {
            $options = Json\Json::decode($json, Json\Json::TYPE_ARRAY);
            $this->setOptions($options);
        } catch (\Exception $e) {
            $this->isParseError = true;
        }
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

        return Json\Json::encode($jsonArray);
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
