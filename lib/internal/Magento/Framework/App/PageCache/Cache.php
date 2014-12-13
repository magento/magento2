<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
