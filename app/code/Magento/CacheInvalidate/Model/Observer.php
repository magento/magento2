<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Model;

use Magento\Framework\Cache\InvalidateLogger;

/**
 * Class Observer
 */
class Observer
{
    /**
     * Application config object
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\PageCache\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    protected $_curlAdapter;

    /**
     * @var InvalidateLogger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param \Magento\PageCache\Model\Config $config
     * @param \Magento\PageCache\Helper\Data $helper
     * @param \Magento\Framework\HTTP\Adapter\Curl $curlAdapter
     * @param InvalidateLogger $logger
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\PageCache\Helper\Data $helper,
        \Magento\Framework\HTTP\Adapter\Curl $curlAdapter,
        InvalidateLogger $logger
    ) {
        $this->_config = $config;
        $this->_helper = $helper;
        $this->_curlAdapter = $curlAdapter;
        $this->logger = $logger;
    }

    /**
     * If Varnish caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function invalidateVarnish(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->_config->isEnabled()) {
            $object = $observer->getEvent()->getObject();
            if ($object instanceof \Magento\Framework\Object\IdentityInterface) {
                $tags = [];
                $pattern = "((^|,)%s(,|$))";
                foreach ($object->getIdentities() as $tag) {
                    $tags[] = sprintf($pattern, preg_replace("~_\\d+$~", '', $tag));
                    $tags[] = sprintf($pattern, $tag);
                }
                $this->sendPurgeRequest(implode('|', array_unique($tags)));
            }
        }
    }

    /**
     * Flash Varnish cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function flushAllCache(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_config->getType() == \Magento\PageCache\Model\Config::VARNISH && $this->_config->isEnabled()) {
            $this->sendPurgeRequest('.*');
        }
    }

    /**
     * Send curl purge request
     * to invalidate cache by tags pattern
     *
     * @param string $tagsPattern
     * @return void
     */
    protected function sendPurgeRequest($tagsPattern)
    {
        $headers = ["X-Magento-Tags-Pattern: {$tagsPattern}"];
        $this->_curlAdapter->setOptions([CURLOPT_CUSTOMREQUEST => 'PURGE']);
        $this->_curlAdapter->write('', $this->_helper->getUrl('*'), '1.1', $headers);
        $this->_curlAdapter->read();
        $this->_curlAdapter->close();

        $this->logger->execute(compact('tagsPattern'));
    }
}
