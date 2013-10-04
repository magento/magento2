<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Proxy class for \Magento\Core\Controller\Request\Http
 */
namespace Magento\Core\Controller\Request;

class HttpProxy extends \Magento\Core\Controller\Request\Http
{
    /**
     * Object Manager instance
     *
     * @var \Magento\ObjectManager
     */
    protected $_objectManager = null;

    /**
     * Proxied instance name
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Proxied instance
     *
     * @var \Magento\Core\Controller\Request\Http
     */
    protected $_subject = null;

    /**
     * Instance shareability flag
     *
     * @var bool
     */
    protected $_isShared = null;

    /**
     * Proxy constructor
     *
     * @param \Magento\ObjectManager $objectManager
     * @param string $instanceName
     * @param bool $shared
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        $instanceName = 'Magento\Core\Controller\Request\Http', $shared = true
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
        $this->_isShared = $shared;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return array('_subject', '_isShared');
    }

    /**
     * Retrieve ObjectManager from global scope
     */
    public function __wakeup()
    {
        $this->_objectManager = \Magento\Core\Model\ObjectManager::getInstance();
    }

    /**
     * Clone proxied instance
     */
    public function __clone()
    {
        $this->_subject = clone $this->_getSubject();
    }

    /**
     * Get proxied instance
     *
     * @return \Magento\Core\Controller\Request\Http
     */
    protected function _getSubject()
    {
        if (!$this->_subject) {
            $this->_subject = true === $this->_isShared
                ? $this->_objectManager->get($this->_instanceName)
                : $this->_objectManager->create($this->_instanceName);
        }
        return $this->_subject;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalPathInfo()
    {
        return $this->_getSubject()->getOriginalPathInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function setPathInfo($pathInfo = null)
    {
        return $this->_getSubject()->setPathInfo($pathInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function rewritePathInfo($pathInfo)
    {
        return $this->_getSubject()->rewritePathInfo($pathInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectAccessFrontendName($code)
    {
        return $this->_getSubject()->isDirectAccessFrontendName($code);
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectFrontNames()
    {
        return $this->_getSubject()->getDirectFrontNames();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestString()
    {
        return $this->_getSubject()->getRequestString();
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePath()
    {
        return $this->_getSubject()->getBasePath();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return $this->_getSubject()->getBaseUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteName($route)
    {
        return $this->_getSubject()->setRouteName($route);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteName()
    {
        return $this->_getSubject()->getRouteName();
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpHost($trimPort = true)
    {
        return $this->_getSubject()->getHttpHost($trimPort);
    }

    /**
     * {@inheritdoc}
     */
    public function setPost($key, $value = null)
    {
        return $this->_getSubject()->setPost($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerModule($module)
    {
        return $this->_getSubject()->setControllerModule($module);
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerModule()
    {
        return $this->_getSubject()->getControllerModule();
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleName()
    {
        return $this->_getSubject()->getModuleName();
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerName()
    {
        return $this->_getSubject()->getControllerName();
    }

    /**
     * {@inheritdoc}
     */
    public function getActionName()
    {
        return $this->_getSubject()->getActionName();
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias($name)
    {
        return $this->_getSubject()->getAlias($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return $this->_getSubject()->getAliases();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestedRouteName()
    {
        return $this->_getSubject()->getRequestedRouteName();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestedControllerName()
    {
        return $this->_getSubject()->getRequestedControllerName();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestedActionName()
    {
        return $this->_getSubject()->getRequestedActionName();
    }

    /**
     * {@inheritdoc}
     */
    public function setRoutingInfo($data)
    {
        return $this->_getSubject()->setRoutingInfo($data);
    }

    /**
     * {@inheritdoc}
     */
    public function initForward()
    {
        return $this->_getSubject()->initForward();
    }

    /**
     * {@inheritdoc}
     */
    public function getBeforeForwardInfo($name = null)
    {
        return $this->_getSubject()->getBeforeForwardInfo($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isStraight($flag = null)
    {
        return $this->_getSubject()->isStraight($flag);
    }

    /**
     * {@inheritdoc}
     */
    public function isAjax()
    {
        return $this->_getSubject()->isAjax();
    }

    /**
     * {@inheritdoc}
     */
    public function getFiles($key = null, $default = null)
    {
        return $this->_getSubject()->getFiles($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getDistroBaseUrl()
    {
        return $this->_getSubject()->getDistroBaseUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function __get($key)
    {
        return $this->_getSubject()->__get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->_getSubject()->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($key, $value)
    {
        return $this->_getSubject()->__set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        return $this->_getSubject()->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function __isset($key)
    {
        return $this->_getSubject()->__isset($key);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return $this->_getSubject()->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery($spec, $value = null)
    {
        return $this->_getSubject()->setQuery($spec, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery($key = null, $default = null)
    {
        return $this->_getSubject()->getQuery($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getPost($key = null, $default = null)
    {
        return $this->_getSubject()->getPost($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getCookie($key = null, $default = null)
    {
        return $this->_getSubject()->getCookie($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getServer($key = null, $default = null)
    {
        return $this->_getSubject()->getServer($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnv($key = null, $default = null)
    {
        return $this->_getSubject()->getEnv($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestUri($requestUri = null)
    {
        return $this->_getSubject()->setRequestUri($requestUri);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestUri()
    {
        return $this->_getSubject()->getRequestUri();
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseUrl($baseUrl = null)
    {
        return $this->_getSubject()->setBaseUrl($baseUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function setBasePath($basePath = null)
    {
        return $this->_getSubject()->setBasePath($basePath);
    }

    /**
     * {@inheritdoc}
     */
    public function getPathInfo()
    {
        return $this->_getSubject()->getPathInfo();
    }

    /**
     * {@inheritdoc}
     */
    public function setParamSources(array $paramSources = array())
    {
        return $this->_getSubject()->setParamSources($paramSources);
    }

    /**
     * {@inheritdoc}
     */
    public function getParamSources()
    {
        return $this->_getSubject()->getParamSources();
    }

    /**
     * {@inheritdoc}
     */
    public function setParam($key, $value)
    {
        return $this->_getSubject()->setParam($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getParam($key, $default = null)
    {
        return $this->_getSubject()->getParam($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getParams()
    {
        return $this->_getSubject()->getParams();
    }

    /**
     * {@inheritdoc}
     */
    public function setParams(array $params)
    {
        return $this->_getSubject()->setParams($params);
    }

    /**
     * {@inheritdoc}
     */
    public function setAlias($name, $target)
    {
        return $this->_getSubject()->setAlias($name, $target);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->_getSubject()->getMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function isPost()
    {
        return $this->_getSubject()->isPost();
    }

    /**
     * {@inheritdoc}
     */
    public function isGet()
    {
        return $this->_getSubject()->isGet();
    }

    /**
     * {@inheritdoc}
     */
    public function isPut()
    {
        return $this->_getSubject()->isPut();
    }

    /**
     * {@inheritdoc}
     */
    public function isDelete()
    {
        return $this->_getSubject()->isDelete();
    }

    /**
     * {@inheritdoc}
     */
    public function isHead()
    {
        return $this->_getSubject()->isHead();
    }

    /**
     * {@inheritdoc}
     */
    public function isOptions()
    {
        return $this->_getSubject()->isOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function isXmlHttpRequest()
    {
        return $this->_getSubject()->isXmlHttpRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function isFlashRequest()
    {
        return $this->_getSubject()->isFlashRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function isSecure()
    {
        return $this->_getSubject()->isSecure();
    }

    /**
     * {@inheritdoc}
     */
    public function getRawBody()
    {
        return $this->_getSubject()->getRawBody();
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($header)
    {
        return $this->_getSubject()->getHeader($header);
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme()
    {
        return $this->_getSubject()->getScheme();
    }

    /**
     * {@inheritdoc}
     */
    public function getClientIp($checkProxy = true)
    {
        return $this->_getSubject()->getClientIp($checkProxy);
    }

    /**
     * {@inheritdoc}
     */
    public function setModuleName($value)
    {
        return $this->_getSubject()->setModuleName($value);
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerName($value)
    {
        return $this->_getSubject()->setControllerName($value);
    }

    /**
     * {@inheritdoc}
     */
    public function setActionName($value)
    {
        return $this->_getSubject()->setActionName($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleKey()
    {
        return $this->_getSubject()->getModuleKey();
    }

    /**
     * {@inheritdoc}
     */
    public function setModuleKey($key)
    {
        return $this->_getSubject()->setModuleKey($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerKey()
    {
        return $this->_getSubject()->getControllerKey();
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerKey($key)
    {
        return $this->_getSubject()->setControllerKey($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionKey()
    {
        return $this->_getSubject()->getActionKey();
    }

    /**
     * {@inheritdoc}
     */
    public function setActionKey($key)
    {
        return $this->_getSubject()->setActionKey($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserParams()
    {
        return $this->_getSubject()->getUserParams();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserParam($key, $default = null)
    {
        return $this->_getSubject()->getUserParam($key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function clearParams()
    {
        return $this->_getSubject()->clearParams();
    }

    /**
     * {@inheritdoc}
     */
    public function setDispatched($flag = true)
    {
        return $this->_getSubject()->setDispatched($flag);
    }

    /**
     * {@inheritdoc}
     */
    public function isDispatched()
    {
        return $this->_getSubject()->isDispatched();
    }
}
