<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache;

class InstanceFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
