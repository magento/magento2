<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Model;

use Magento\Framework\Cache\InvalidateLogger;

class PurgeCache
{
    /**
     * @var \Magento\PageCache\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    protected $curlAdapter;

    /**
     * @var InvalidateLogger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param \Magento\PageCache\Helper\Data $helper
     * @param \Magento\Framework\HTTP\Adapter\Curl $curlAdapter
     * @param InvalidateLogger $logger
     */
    public function __construct(
        \Magento\PageCache\Helper\Data $helper,
        \Magento\Framework\HTTP\Adapter\Curl $curlAdapter,
        InvalidateLogger $logger
    ) {
        $this->helper = $helper;
        $this->curlAdapter = $curlAdapter;
        $this->logger = $logger;
    }

    /**
     * Send curl purge request
     * to invalidate cache by tags pattern
     *
     * @param string $tagsPattern
     * @return void
     */
    public function sendPurgeRequest($tagsPattern)
    {
        $headers = ["X-Magento-Tags-Pattern: {$tagsPattern}"];
        $this->curlAdapter->setOptions([CURLOPT_CUSTOMREQUEST => 'PURGE']);
        $this->curlAdapter->write('', $this->helper->getUrl('*'), '1.1', $headers);
        $this->curlAdapter->read();
        $this->curlAdapter->close();

        $this->logger->execute(compact('tagsPattern'));
    }
}
