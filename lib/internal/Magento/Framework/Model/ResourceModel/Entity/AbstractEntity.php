<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Entity;

abstract class AbstractEntity
{
    /**
     * @var string
     */
    protected $_name = null;

    /**
     * Configuration object
     *
     * @var \Magento\Framework\Simplexml\Config
     */
    protected $_config = [];

    /**
     * Set config
     *
     * @param \Magento\Framework\Simplexml\Config $config
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
