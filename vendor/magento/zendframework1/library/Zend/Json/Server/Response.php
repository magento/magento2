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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Json
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Json_Server_Response
{
    /**
     * Response error
     * @var null|Zend_Json_Server_Error
     */
    protected $_error;

    /**
     * Request ID
     * @var mixed
     */
    protected $_id;

    /**
     * Result
     * @var mixed
     */
    protected $_result;

    /**
     * Service map
     * @var Zend_Json_Server_Smd
     */
    protected $_serviceMap;

    /**
     * JSON-RPC version
     * @var string
     */
    protected $_version;

    /**
     * Set result
     *
     * @param  mixed $value
     * @return Zend_Json_Server_Response
     */
    public function setResult($value)
    {
        $this->_result = $value;
        return $this;
    }

    /**
     * Get result
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->_result;
    }

    // RPC error, if response results in fault
    /**
     * Set result error
     *
     * @param  Zend_Json_Server_Error $error
     * @return Zend_Json_Server_Response
     */
    public function setError(Zend_Json_Server_Error $error)
    {
        $this->_error = $error;
        return $this;
    }

    /**
     * Get response error
     *
     * @return null|Zend_Json_Server_Error
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Is the response an error?
     *
     * @return bool
     */
    public function isError()
    {
        return $this->getError() instanceof Zend_Json_Server_Error;
    }

    /**
     * Set request ID
     *
     * @param  mixed $name
     * @return Zend_Json_Server_Response
     */
    public function setId($name)
    {
        $this->_id = $name;
        return $this;
    }

    /**
     * Get request ID
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
     * @return Zend_Json_Server_Response
     */
    public function setVersion($version)
    {
        $version = is_array($version)
            ? implode(' ', $version)
            : $version;
        if ((string)$version == '2.0') {
            $this->_version = '2.0';
        } else {
            $this->_version = null;
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

        #require_once 'Zend/Json.php';
        return Zend_Json::encode($response);
    }

    /**
     * Retrieve args
     *
     * @return mixed
     */
    public function getArgs()
    {
        return $this->_args;
    }

    /**
     * Set args
     *
     * @param mixed $args
     * @return self
     */
    public function setArgs($args)
    {
        $this->_args = $args;
        return $this;
    }

    /**
     * Set service map object
     *
     * @param  Zend_Json_Server_Smd $serviceMap
     * @return Zend_Json_Server_Response
     */
    public function setServiceMap($serviceMap)
    {
        $this->_serviceMap = $serviceMap;
        return $this;
    }

    /**
     * Retrieve service map
     *
     * @return Zend_Json_Server_Smd|null
     */
    public function getServiceMap()
    {
        return $this->_serviceMap;
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

