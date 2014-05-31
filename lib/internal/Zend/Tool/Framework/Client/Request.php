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
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Request.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Client_Request
{

    /**
     * @var string
     */
    protected $_providerName = null;

    /**
     * @var string
     */
    protected $_specialtyName = null;

    /**
     * @var string
     */
    protected $_actionName = null;

    /**
     * @var array
     */
    protected $_actionParameters = array();

    /**
     * @var array
     */
    protected $_providerParameters = array();

    /**
     * @var bool
     */
    protected $_isPretend = false;

    /**
     * @var bool
     */
    protected $_isDebug = false;

    /**
     * @var bool
     */
    protected $_isVerbose = false;

    /**
     * @var bool
     */
    protected $_isDispatchable = true;

    /**
     * setProviderName()
     *
     * @param string $providerName
     * @return Zend_Tool_Framework_Client_Request
     */
    public function setProviderName($providerName)
    {
        $this->_providerName = $providerName;
        return $this;
    }

    /**
     * getProviderName()
     *
     * @return string
     */
    public function getProviderName()
    {
        return $this->_providerName;
    }

    /**
     * setSpecialtyName()
     *
     * @param string $specialtyName
     * @return Zend_Tool_Framework_Client_Request
     */
    public function setSpecialtyName($specialtyName)
    {
        $this->_specialtyName = $specialtyName;
        return $this;
    }

    /**
     * getSpecialtyName()
     *
     * @return string
     */
    public function getSpecialtyName()
    {
        return $this->_specialtyName;
    }

    /**
     * setActionName()
     *
     * @param string $actionName
     * @return Zend_Tool_Framework_Client_Request
     */
    public function setActionName($actionName)
    {
        $this->_actionName = $actionName;
        return $this;
    }

    /**
     * getActionName()
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->_actionName;
    }

    /**
     * setActionParameter()
     *
     * @param string $parameterName
     * @param string $parameterValue
     * @return Zend_Tool_Framework_Client_Request
     */
    public function setActionParameter($parameterName, $parameterValue)
    {
        $this->_actionParameters[$parameterName] = $parameterValue;
        return $this;
    }

    /**
     * getActionParameters()
     *
     * @return array
     */
    public function getActionParameters()
    {
        return $this->_actionParameters;
    }

    /**
     * getActionParameter()
     *
     * @param string $parameterName
     * @return string
     */
    public function getActionParameter($parameterName)
    {
        return (isset($this->_actionParameters[$parameterName])) ? $this->_actionParameters[$parameterName] : null;
    }

    /**
     * setProviderParameter()
     *
     * @param string $parameterName
     * @param string $parameterValue
     * @return Zend_Tool_Framework_Client_Request
     */
    public function setProviderParameter($parameterName, $parameterValue)
    {
        $this->_providerParameters[$parameterName] = $parameterValue;
        return $this;
    }

    /**
     * getProviderParameters()
     *
     * @return array
     */
    public function getProviderParameters()
    {
        return $this->_providerParameters;
    }

    /**
     * getProviderParameter()
     *
     * @param string $parameterName
     * @return string
     */
    public function getProviderParameter($parameterName)
    {
        return (isset($this->_providerParameters[$parameterName])) ? $this->_providerParameters[$parameterName] : null;
    }

    /**
     * setPretend()
     *
     * @param bool $pretend
     * @return Zend_Tool_Framework_Client_Request
     */
    public function setPretend($pretend)
    {
        $this->_isPretend = (bool) $pretend;
        return $this;
    }

    /**
     * isPretend() - Whether or not this is a pretend request
     *
     * @return bool
     */
    public function isPretend()
    {
        return $this->_isPretend;
    }

    /**
     * setDebug()
     *
     * @param bool $pretend
     * @return Zend_Tool_Framework_Client_Request
     */
    public function setDebug($debug)
    {
        $this->_isDebug = (bool) $debug;
        return $this;
    }

    /**
     * isDebug() - Whether or not this is a debug enabled request
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this->_isDebug;
    }

    /**
     * setVerbose()
     *
     * @param bool $verbose
     * @return Zend_Tool_Framework_Client_Request
     */
    public function setVerbose($verbose)
    {
        $this->_isVerbose = (bool) $verbose;
        return $this;
    }

    /**
     * isVerbose() - Whether or not this is a verbose enabled request
     *
     * @return bool
     */
    public function isVerbose()
    {
        return $this->_isVerbose;
    }

    /**
     * setDispatchable()
     *
     * @param bool $dispatchable
     * @return Zend_Tool_Framework_Client_Request
     */
    public function setDispatchable($dispatchable)
    {
        $this->_isDispatchable = (bool) $dispatchable;
        return $this;
    }

    /**
     * isDispatchable() Is this request Dispatchable?
     *
     * @return bool
     */
    public function isDispatchable()
    {
        return $this->_isDispatchable;
    }

}