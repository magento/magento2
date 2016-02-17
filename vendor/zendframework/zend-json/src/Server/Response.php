<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Json\Server;

use Zend\Json\Json;

class Response
{
    /**
     * Response error
     * @var null|Error
     */
    protected $error;

    /**
     * Request ID
     * @var mixed
     */
    protected $id;

    /**
     * Result
     * @var mixed
     */
    protected $result;

    /**
     * Service map
     * @var Smd
     */
    protected $serviceMap;

    /**
     * JSON-RPC version
     * @var string
     */
    protected $version;

    /**
     * @var $args
     */
    protected $args;

    /**
     * Set response state
     *
     * @param  array $options
     * @return Response
     */
    public function setOptions(array $options)
    {
        // re-produce error state
        if (isset($options['error']) && is_array($options['error'])) {
            $error = $options['error'];
            $options['error'] = new Error($error['message'], $error['code'], $error['data']);
        }

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
     * Set response state based on JSON
     *
     * @param  string $json
     * @return void
     * @throws Exception\RuntimeException
     */
    public function loadJson($json)
    {
        $options = Json::decode($json, Json::TYPE_ARRAY);

        if (!is_array($options)) {
            throw new Exception\RuntimeException('json is not a valid response; array expected');
        }

        $this->setOptions($options);
    }

    /**
     * Set result
     *
     * @param  mixed $value
     * @return Response
     */
    public function setResult($value)
    {
        $this->result = $value;
        return $this;
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

    // RPC error, if response results in fault
    /**
     * Set result error
     *
     * @param  mixed $error
     * @return Response
     */
    public function setError(Error $error = null)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * Get response error
     *
     * @return null|Error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Is the response an error?
     *
     * @return bool
     */
    public function isError()
    {
        return $this->getError() instanceof Error;
    }

    /**
     * Set request ID
     *
     * @param  mixed $name
     * @return Response
     */
    public function setId($name)
    {
        $this->id = $name;
        return $this;
    }

    /**
     * Get request ID
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
     * @return Response
     */
    public function setVersion($version)
    {
        $version = (string) $version;
        if ('2.0' == $version) {
            $this->version = '2.0';
        } else {
            $this->version = null;
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
     * Cast to JSON
     *
     * @return string
     */
    public function toJson()
    {
        if ($this->isError()) {
            $response = array(
                'error'  => $this->getError()->toArray(),
                'id'     => $this->getId(),
            );
        } else {
            $response = array(
                'result' => $this->getResult(),
                'id'     => $this->getId(),
            );
        }

        if (null !== ($version = $this->getVersion())) {
            $response['jsonrpc'] = $version;
        }

        return \Zend\Json\Json::encode($response);
    }

    /**
     * Retrieve args
     *
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Set args
     *
     * @param mixed $args
     * @return self
     */
    public function setArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    /**
     * Set service map object
     *
     * @param  Smd $serviceMap
     * @return Response
     */
    public function setServiceMap($serviceMap)
    {
        $this->serviceMap = $serviceMap;
        return $this;
    }

    /**
     * Retrieve service map
     *
     * @return Smd|null
     */
    public function getServiceMap()
    {
        return $this->serviceMap;
    }

    /**
     * Cast to string (JSON)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
