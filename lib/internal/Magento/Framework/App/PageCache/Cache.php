<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

/**
 * Cache model for builtin cache
 */
class Cache extends \Magento\Framework\App\Cache
{
    /**
     * @var string
     */
    protected $_frontendIdentifier = 'page_cache';
}
