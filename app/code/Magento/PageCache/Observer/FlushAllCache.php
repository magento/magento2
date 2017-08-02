<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\PageCache\Observer\FlushAllCache
 *
 * @since 2.0.0
 */
class FlushAllCache implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\PageCache\Cache
     *
     * @deprecated 2.1.0
     * @since 2.0.0
     */
    protected $_cache;

    /**
     * Application config object
     *
     * @var \Magento\PageCache\Model\Config
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @var \Magento\PageCache\Model\Cache\Type
     * @since 2.1.0
     */
    private $fullPageCache;

    /**
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\Framework\App\PageCache\Cache $cache
     * @since 2.0.0
     */
    public function __construct(\Magento\PageCache\Model\Config $config, \Magento\Framework\App\PageCache\Cache $cache)
    {
        $this->_config = $config;
        $this->_cache = $cache;
    }

    /**
     * Flash Built-In cache
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_config->getType() == \Magento\PageCache\Model\Config::BUILT_IN) {
            $this->getCache()->clean();
        }
    }

    /**
     * TODO: Workaround to support backwards compatibility, will rework to use Dependency Injection in MAGETWO-49547
     *
     * @return \Magento\PageCache\Model\Cache\Type
     * @since 2.1.0
     */
    private function getCache()
    {
        if (!$this->fullPageCache) {
            $this->fullPageCache = ObjectManager::getInstance()->get(\Magento\PageCache\Model\Cache\Type::class);
        }
        return $this->fullPageCache;
    }
}
