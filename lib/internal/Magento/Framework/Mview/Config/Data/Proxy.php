<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Config\Data;

/**
 * Proxy class for \Magento\Framework\Mview\Config\Data
 */
class Proxy extends \Magento\Framework\Mview\Config\Data
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Proxied instance name
     *
     * @var string
     */
    protected $instanceName;

    /**
     * Proxied instance
     *
     * @var \Magento\Framework\Mview\Config\Data
     */
    protected $subject;

    /**
     * Instance shareability flag
     *
     * @var bool
     */
    protected $isShared = null;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     * @param bool $shared
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = 'Magento\Framework\Mview\Config\Data',
        $shared = true
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->isShared = $shared;
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
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Clone proxied instance
     *
     * @return void
     */
    public function __clone()
    {
        $this->subject = clone $this->_getSubject();
    }

    /**
     * Get proxied instance
     *
     * @return \Magento\Framework\Mview\Config\Data
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
     */
    public function merge(array $config)
    {
        $this->_getSubject()->merge($config);
    }

    /**
     * {@inheritdoc}
     */
    public function get($path = null, $default = null)
    {
        return $this->_getSubject()->get($path, $default);
    }
}
