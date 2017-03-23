<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Page cache data helper
 *
 */
namespace Magento\PageCache\Helper;

/**
 * Helper for Page Cache module
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Private caching time one year
     */
    const PRIVATE_MAX_AGE_CACHE = 31536000;

    /**
     * @var \Magento\Framework\Session\Config
     */
    protected $config;

    /**
     * @param \Magento\Framework\Session\Config $config
     */
    public function __construct(\Magento\Framework\Session\Config $config)
    {
        $this->config;
    }

    /**
     * Retrieve url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route, array $params = [])
    {
        return $this->_getUrl($route, $params);
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->config->getValidDomain();
    }
}
