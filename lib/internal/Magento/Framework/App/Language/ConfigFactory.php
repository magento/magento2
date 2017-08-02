<?php
/**
 * Application language config factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Language;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class ConfigFactory
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
     * Create config
     *
     * @param array $arguments
     * @return Config
     * @since 2.0.0
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create(\Magento\Framework\App\Language\Config::class, $arguments);
    }
}
