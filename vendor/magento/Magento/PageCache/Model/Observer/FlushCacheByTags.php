<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\PageCache\Model\Observer;

class FlushCacheByTags
{
    /**
     * @var \Magento\Framework\App\PageCache\Cache
     */
    protected $_cache;

    /**
     * Application config object
     *
     * @var \Magento\PageCache\Model\Config
     */
    protected $_config;

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
     * If Built-In caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_config->getType() == \Magento\PageCache\Model\Config::BUILT_IN && $this->_config->isEnabled()) {
            $object = $observer->getEvent()->getObject();
            if ($object instanceof \Magento\Framework\Object\IdentityInterface) {
                $tags = $object->getIdentities();
                foreach ($tags as $tag) {
                    $tags[] = preg_replace("~_\\d+$~", '', $tag);
                }
                $this->_cache->clean(array_unique($tags));
            }
        }
    }
}
