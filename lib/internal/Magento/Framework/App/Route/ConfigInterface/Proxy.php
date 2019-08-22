<?php
/**
 * Routes configuration model proxy
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Route\ConfigInterface;

/**
 * Proxy class for \Magento\Framework\App\ResourceConnection
 */
class Proxy implements
    \Magento\Framework\App\Route\ConfigInterface,
    \Magento\Framework\ObjectManager\NoninterceptableInterface
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
     * @var \Magento\Framework\App\ResourceConnection
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
        $instanceName = \Magento\Framework\App\Route\ConfigInterface::class,
        $shared = true
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
        $this->_isShared = $shared;
    }

    /**
     * Sleep magic method.
     *
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
     * @return \Magento\Framework\App\Route\ConfigInterface
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
     * Retrieve route front name
     *
     * @param string $routeId
     * @param string $scope
     * @return string
     */
    public function getRouteFrontName($routeId, $scope = null)
    {
        return $this->_getSubject()->getRouteFrontName($routeId, $scope);
    }

    /**
     * Get route id by route front name
     *
     * @param string $frontName
     * @param string $scope
     * @return string
     */
    public function getRouteByFrontName($frontName, $scope = null)
    {
        return $this->_getSubject()->getRouteByFrontName($frontName, $scope);
    }

    /**
     * Retrieve list of modules by route front name
     *
     * @param string $frontName
     * @param string $scope
     * @return array
     */
    public function getModulesByFrontName($frontName, $scope = null)
    {
        $this->_getSubject()->getModulesByFrontName($frontName, $scope);
    }
}
