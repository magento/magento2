<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache;

/**
 * Class \Magento\Framework\App\Cache\InstanceFactory
 *
 * @since 2.0.0
 */
class InstanceFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get cache instance model
     *
     * @param string $instanceName
     * @return \Magento\Framework\Cache\FrontendInterface
     * @throws \UnexpectedValueException
     * @since 2.0.0
     */
    public function get($instanceName)
    {
        $instance = $this->_objectManager->get($instanceName);
        if (!$instance instanceof \Magento\Framework\Cache\FrontendInterface) {
            throw new \UnexpectedValueException("Cache type class '{$instanceName}' has to be a cache frontend.");
        }

        return $instance;
    }
}
