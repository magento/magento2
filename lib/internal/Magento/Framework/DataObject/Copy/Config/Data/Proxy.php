<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject\Copy\Config\Data;

/**
 * Proxy class for @see \Magento\Framework\DataObject\Copy\Config\Data
 * @since 2.0.0
 */
class Proxy extends \Magento\Framework\DataObject\Copy\Config\Data implements
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
     * @var \Magento\Framework\DataObject\Copy\Config\Data
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
        $instanceName = \Magento\Framework\DataObject\Copy\Config\Data::class,
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
     * @return \Magento\Framework\DataObject\Copy\Config\Data
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
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function merge(array $config)
    {
        return $this->_getSubject()->merge($config);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($path = null, $default = null)
    {
        return $this->_getSubject()->get($path, $default);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function reset()
    {
        return $this->_getSubject()->reset();
    }
}
