<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Page cache data helper
 *
 */
namespace Magento\PageCache\Helper;

/**
 * Helper for Page Cache module
 * @since 2.0.0
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Private caching time one year
     */
    const PRIVATE_MAX_AGE_CACHE = 31536000;

    /**
     * @var \Magento\Framework\Session\Config
     * @since 2.1.0
     */
    protected $config;

    /**
     * @param \Magento\Framework\Session\Config $config
     * @since 2.1.0
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
     * @since 2.0.0
     */
    public function getUrl($route, array $params = [])
    {
        return $this->_getUrl($route, $params);
    }

    /**
     * @return string
     * @since 2.1.0
     */
    public function getDomain()
    {
        return $this->config->getValidDomain();
    }
}
