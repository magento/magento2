<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\UrlInterface;

/**
 * Proxy class for @see \Magento\Framework\UrlInterface
 */
class Proxy implements \Magento\Framework\UrlInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
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
     * @var \Magento\Framework\UrlInterface
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
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = '\\Magento\\Framework\\UrlInterface',
        $shared = true
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
        return ['_subject', '_isShared'];
    }

    /**
     * Retrieve ObjectManager from global scope
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Clone proxied instance
     *
     * @return void
     */
    public function __clone()
    {
        $this->_subject = clone $this->_getSubject();
    }

    /**
     * Get proxied instance
     *
     * @return \Magento\Framework\UrlInterface
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
    public function getUseSession()
    {
        return $this->_getSubject()->getUseSession();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl($params = [])
    {
        return $this->_getSubject()->getBaseUrl($params);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentUrl()
    {
        return $this->_getSubject()->getCurrentUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteUrl($routePath = null, $routeParams = null)
    {
        return $this->_getSubject()->getRouteUrl($routePath, $routeParams);
    }

    /**
     * {@inheritdoc}
     */
    public function addSessionParam()
    {
        return $this->_getSubject()->addSessionParam();
    }

    /**
     * {@inheritdoc}
     */
    public function addQueryParams(array $data)
    {
        return $this->_getSubject()->addQueryParams($data);
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryParam($key, $data)
    {
        return $this->_getSubject()->setQueryParam($key, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl($routePath = null, $routeParams = null)
    {
        return $this->_getSubject()->getUrl($routePath, $routeParams);
    }

    /**
     * {@inheritdoc}
     */
    public function escape($value)
    {
        return $this->_getSubject()->escape($value);
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectUrl($url, $params = [])
    {
        return $this->_getSubject()->getDirectUrl($url, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function sessionUrlVar($html)
    {
        return $this->_getSubject()->sessionUrlVar($html);
    }

    /**
     * {@inheritdoc}
     */
    public function isOwnOriginUrl()
    {
        return $this->_getSubject()->isOwnOriginUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUrl($url)
    {
        return $this->_getSubject()->getRedirectUrl($url);
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($params)
    {
        return $this->_getSubject()->setScope($params);
    }
}
