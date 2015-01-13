<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Observer;

class FlushAllCache
{
    /**
     * @var \Magento\Framework\App\PageCache\Cache
     */
    protected $_cache;

    /**
     * Application config object
     *
     * @var \Magento\PageCache\Model\Config
     */
    protected $_config;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\PageCache\Cache $cache
     */
    public function __construct(\Magento\PageCache\Model\Config $config, \Magento\Framework\App\PageCache\Cache $cache)
    {
        $this->_config = $config;
        $this->_cache = $cache;
    }

    /**
     * Flash Built-In cache
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_config->getType() == \Magento\PageCache\Model\Config::BUILT_IN) {
            $this->_cache->clean();
        }
    }
}
