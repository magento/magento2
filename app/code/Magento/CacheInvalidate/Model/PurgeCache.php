<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Model;

use Zend\Uri\Uri;
use Magento\Framework\Cache\InvalidateLogger;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\RequestInterface;
use Zend\Uri\UriFactory;

class PurgeCache
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var SocketFactory
     */
    protected $socketAdapterFactory;

    /**
     * @var InvalidateLogger
     */
    private $logger;

    /**
     * @var DeploymentConfig
     */
    private $config;

    /**
     * @var RequestInterface
     */
    private $request;

    const DEFAULT_PORT = 80;

    /**
     * Constructor
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param SocketFactory $socketAdapterFactory
     * @param InvalidateLogger $logger
     * @param DeploymentConfig $config
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        SocketFactory $socketAdapterFactory,
        InvalidateLogger $logger,
        DeploymentConfig $config,
        RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->socketAdapterFactory = $socketAdapterFactory;
        $this->logger = $logger;
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Send curl purge request
     * to invalidate cache by tags pattern
     *
     * @param string $tagsPattern
     * @return bool Return true if successful; otherwise return false
     */
    public function sendPurgeRequest($tagsPattern)
    {
        $socketAdapter = $this->socketAdapterFactory->create();
        $servers = $this->getServers();
        $headers = ['X-Magento-Tags-Pattern' => $tagsPattern];
        $socketAdapter->setOptions(['timeout' => 10]);
        foreach ($servers as $server) {
            try {
                $socketAdapter->connect($server->getHost(), $server->getPort());
                $socketAdapter->write(
                    'PURGE',
                    $server,
                    '1.1',
                    $headers
                );
                $socketAdapter->close();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage(), compact('server', 'tagsPattern'));
                return false;
            }
        }

        $this->logger->execute(compact('servers', 'tagsPattern'));
        return true;
    }

    /**
     * Get cache servers' uris
     *
     * @return Uri[]
     */
    private function getServers()
    {
        $servers = [];
        $configedHosts = $this->config->get(ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS);
        if (null == $configedHosts) {
            $httpHost = $this->request->getHttpHost();
            $servers[] = $httpHost ?
                UriFactory::factory('')->setHost($httpHost)->setPort(self::DEFAULT_PORT)->setScheme('http') :
                UriFactory::factory($this->urlBuilder->getUrl('*'));
        } else {
            foreach ($configedHosts as $host) {
                $servers[] = UriFactory::factory('')->setHost($host['host'])
                    ->setPort(isset($host['port']) ? $host['port'] : self::DEFAULT_PORT)
                    ->setScheme('http');
            }
        }
        return $servers;
    }
}
