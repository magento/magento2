<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Config\Data;

/**
 * Proxy class for \Magento\Framework\Mview\Config\Data
 * @since 2.0.0
 */
class Proxy extends \Magento\Framework\Mview\Config\Data implements
    \Magento\Framework\ObjectManager\NoninterceptableInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * Proxied instance name
     *
     * @var string
     * @since 2.0.0
     */
    protected $instanceName;

    /**
     * Proxied instance
     *
     * @var \Magento\Framework\Mview\Config\Data
     * @since 2.0.0
     */
    protected $subject;

    /**
     * Instance shareability flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $isShared = null;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\Mview\Config\Data::class,
        $shared = true
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->isShared = $shared;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function __sleep()
    {
        return ['subject', 'isShared'];
    }

    /**
     * Retrieve ObjectManager from global scope
     *
     * @return void
     * @since 2.0.0
     */
    public function __wakeup()
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Clone proxied instance
     *
     * @return void
     * @since 2.0.0
     */
    public function __clone()
    {
        $this->subject = clone $this->_getSubject();
    }

    /**
     * Get proxied instance
     *
     * @return \Magento\Framework\Mview\Config\Data
     * @since 2.0.0
     */
    protected function _getSubject()
    {
        if (!$this->subject) {
            $this->subject = true === $this->isShared ? $this->objectManager->get(
                $this->instanceName
            ) : $this->objectManager->create(
                $this->instanceName
            );
        }
        return $this->subject;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function merge(array $config)
    {
        $this->_getSubject()->merge($config);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($path = null, $default = null)
    {
        return $this->_getSubject()->get($path, $default);
    }
}
