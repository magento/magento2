<?php
/**
 * Application area list
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\AreaList;

/**
 * Class \Magento\Framework\App\AreaList\Proxy
 *
 * @since 2.0.0
 */
class Proxy extends \Magento\Framework\App\AreaList implements
    \Magento\Framework\ObjectManager\NoninterceptableInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager = null;

    /**
     * Proxied instance name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_instanceName = null;

    /**
     * Proxied instance
     *
     * @var \Magento\Framework\Locale\Resolver
     * @since 2.0.0
     */
    protected $_subject = null;

    /**
     * Instance shareability flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isShared = null;

    /**
     * Proxy constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\App\AreaList::class,
        $shared = true
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
        $this->_isShared = $shared;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function __sleep()
    {
        return ['_subject', '_isShared'];
    }

    /**
     * Retrieve ObjectManager from global scope
     *
     * @return void
     * @since 2.0.0
     */
    public function __wakeup()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Clone proxied instance
     *
     * @return void
     * @since 2.0.0
     */
    public function __clone()
    {
        $this->_subject = clone $this->_getSubject();
    }

    /**
     * Get proxied instance
     *
     * @return \Magento\Framework\Locale\Resolver
     * @since 2.0.0
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
     * Retrieve area code by front name
     *
     * @param string $frontName
     * @return null|string
     * @since 2.0.0
     */
    public function getCodeByFrontName($frontName)
    {
        return $this->_getSubject()->getCodeByFrontName($frontName);
    }

    /**
     * Retrieve area front name by code
     *
     * @param string $areaCode
     * @return string
     * @since 2.0.0
     */
    public function getFrontName($areaCode)
    {
        return $this->_getSubject()->getFrontName($areaCode);
    }

    /**
     * Retrieve area codes
     *
     * @return string[]
     * @since 2.0.0
     */
    public function getCodes()
    {
        return $this->_getSubject()->getCodes();
    }

    /**
     * Retrieve default area router id
     *
     * @param string $areaCode
     * @return string
     * @since 2.0.0
     */
    public function getDefaultRouter($areaCode)
    {
        return $this->_getSubject()->getDefaultRouter($areaCode);
    }

    /**
     * Retrieve application area
     *
     * @param   string $code
     * @return  \Magento\Framework\App\Area
     * @since 2.0.0
     */
    public function getArea($code)
    {
        return $this->_getSubject()->getArea($code);
    }
}
