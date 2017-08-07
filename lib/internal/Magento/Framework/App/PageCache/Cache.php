<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

/**
 * Cache model for builtin cache
 *
 * @deprecated 2.1.0
 */
class Cache extends \Magento\Framework\App\Cache
{
    /**
     * @var string
     *
     * @deprecated 2.1.0
     */
    protected $_frontendIdentifier = 'page_cache';
}
