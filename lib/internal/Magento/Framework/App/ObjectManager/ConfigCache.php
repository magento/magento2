<?php
/**
 * Object manager configuration cache
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager;

class ConfigCache implements \Magento\Framework\ObjectManager\ConfigCacheInterface
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cacheFrontend;

    /**
     * Cache prefix
     *
     * @var string
     */
    protected $_prefix = 'diConfig';

    /**
     * @param \Magento\Framework\Cache\FrontendInterface $cacheFrontend
     */
    public function __construct(\Magento\Framework\Cache\FrontendInterface $cacheFrontend)
    {
        $this->_cacheFrontend = $cacheFrontend;
    }

    /**
     * Retrieve configuration from cache
     *
     * @param string $key
     * @return array
     */
    public function get($key)
    {
        return unserialize($this->_cacheFrontend->load($this->_prefix . $key));
    }

    /**
     * Save config to cache
     *
     * @param array $config
     * @param string $key
     * @return void
     */
    public function save(array $config, $key)
    {
        $this->_cacheFrontend->save(serialize($config), $this->_prefix . $key);
    }
}
