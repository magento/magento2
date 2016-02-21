<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class FlushAllCache implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\PageCache\Cache
     *
     * @deprecated
     */
    protected $_cache;

    /**
     * Application config object
     *
     * @var \Magento\PageCache\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\PageCache\Model\Cache\Type
     */
    private $fullPageCache;

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
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     */
    private function getCache()
    {
        if (!$this->fullPageCache) {
            $this->fullPageCache = ObjectManager::getInstance()->get('\Magento\PageCache\Model\Cache\Type');
        }
        return $this->fullPageCache;
    }

    /**
     * @param \Magento\PageCache\Model\Cache\Type $cache
     * @throws LocalizedException
     */
    public function setCache(\Magento\PageCache\Model\Cache\Type $cache)
    {
        if ($this->fullPageCache) {
            throw new LocalizedException(new Phrase('fullPageCache is already set'));
        }
        $this->fullPageCache = $cache;
    }
}
