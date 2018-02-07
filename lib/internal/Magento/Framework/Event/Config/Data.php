<?php
/**
 * Event configuration data container
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event\Config;

class Data extends \Magento\Framework\Config\Data\Scoped
{
    /**
     * Scope priority loading scheme
     *
     * @var array
     */
    protected $_scopePriorityScheme = ['global'];

    /**
     * @param \Magento\Framework\Event\Config\Reader $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\Event\Config\Reader $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'event_config_cache'
    ) {
        parent::__construct($reader, $configScope, $cache, $cacheId);
    }
}
