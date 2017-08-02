<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Entity;

/**
 * Class \Magento\Framework\Model\ResourceModel\Entity\AbstractEntity
 *
 * @since 2.0.0
 */
abstract class AbstractEntity
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_name = null;

    /**
     * Configuration object
     *
     * @var \Magento\Framework\Simplexml\Config
     * @since 2.0.0
     */
    protected $_config = [];

    /**
     * Set config
     *
     * @param \Magento\Framework\Simplexml\Config $config
     * @since 2.0.0
     */
    public function __construct($config)
    {
        $this->_config = $config;
    }

    /**
     * Get config by key
     *
     * @param string $key
     * @return \Magento\Framework\Simplexml\Config|string|false
     * @since 2.0.0
     */
    public function getConfig($key = '')
    {
        if ('' === $key) {
            return $this->_config;
        } elseif (isset($this->_config->{$key})) {
            return $this->_config->{$key};
        } else {
            return false;
        }
    }
}
