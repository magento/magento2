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
     * @var \Zend\Uri\Uri
     */
    protected $uri;

    /**
     * @var \Zend\Http\Client\Adapter\Socket
     */
    protected $socketAdapter;

    /**
     * @var InvalidateLogger
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Reader
     */
    private $configReader;

    /**
     * Constructor
     *
     * @param \Magento\PageCache\Helper\Data $helper
     * @param \Magento\Framework\HTTP\Adapter\Curl $curlAdapter
     * @param InvalidateLogger $logger
     */
    public function __construct(
        \Zend\Uri\Uri $uri,
        \Zend\Http\Client\Adapter\Socket $socketAdapter,
        InvalidateLogger $logger,
        \Magento\Framework\App\DeploymentConfig\Reader $configReader
    ) {
        $this->uri = $uri;
        $this->socketAdapter = $socketAdapter;
        $this->logger = $logger;
        $this->configReader = $configReader;
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
        $env = $this->configReader->load(\Magento\Framework\Config\File\ConfigFilePool::APP_ENV);
        $hosts = isset($env['cache_servers']) ? $env['cache_servers'] : ['127.0.0.1:80'];

        $headers = ['X-Magento-Tags-Pattern' => $tagsPattern];
        $this->socketAdapter->setOptions(['timeout' => 10]);
        foreach ($hosts as $host) {
            $this->uri->parse('http://' . $host);
            $this->socketAdapter->connect($this->uri->getHost(), $this->uri->getPort() ?: 80);
            $this->socketAdapter->write('PURGE', $this->uri, '1.1', $headers);
            $this->socketAdapter->close();
        }

        $this->logger->execute(compact('tagsPattern'));
    }
}
