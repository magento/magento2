<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Observer;

use Magento\Framework\App\PageCache\Cache;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\PageCache\Model\Cache\Type;
use Magento\PageCache\Model\Config;

/**
 * Observer used to flush all caches with built-in full page cache
 */
class FlushAllCache implements ObserverInterface
{
    /**
     * @var Cache
     *
     * @deprecated 100.1.0
     */
    protected $_cache;

    /**
     * Application config object
     *
     * @var Config
     */
    protected $_config;

    /**
     * @var Type
     */
    private $fullPageCache;

    /**
     * @param Config $config
     * @param Cache $cache
     * @param Type $fullPageCache
     */
    public function __construct(
        Config $config,
        Cache $cache,
        Type $fullPageCache
    ) {
        $this->_config = $config;
        $this->_cache = $cache;
        $this->fullPageCache = $fullPageCache;
    }

    /**
     * Flash Built-In cache
     *
     * @param Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->_config->getType() === Config::BUILT_IN) {
            $this->fullPageCache->clean();
        }
    }
}
