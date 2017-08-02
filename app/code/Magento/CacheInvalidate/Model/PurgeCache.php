<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Model;

use Magento\Framework\Cache\InvalidateLogger;

/**
 * Class \Magento\CacheInvalidate\Model\PurgeCache
 *
 * @since 2.0.0
 */
class PurgeCache
{
    const HEADER_X_MAGENTO_TAGS_PATTERN = 'X-Magento-Tags-Pattern';

    /**
     * @var \Magento\PageCache\Model\Cache\Server
     * @since 2.0.0
     */
    protected $cacheServer;

    /**
     * @var \Magento\CacheInvalidate\Model\SocketFactory
     * @since 2.0.0
     */
    protected $socketAdapterFactory;

    /**
     * @var InvalidateLogger
     * @since 2.0.0
     */
    private $logger;

    /**
     * Constructor
     *
     * @param \Magento\PageCache\Model\Cache\Server $cacheServer
     * @param \Magento\CacheInvalidate\Model\SocketFactory $socketAdapterFactory
     * @param InvalidateLogger $logger
     * @since 2.0.0
     */
    public function __construct(
        \Magento\PageCache\Model\Cache\Server $cacheServer,
        \Magento\CacheInvalidate\Model\SocketFactory $socketAdapterFactory,
        InvalidateLogger $logger
    ) {
        $this->cacheServer = $cacheServer;
        $this->socketAdapterFactory = $socketAdapterFactory;
        $this->logger = $logger;
    }

    /**
     * Send curl purge request
     * to invalidate cache by tags pattern
     *
     * @param string $tagsPattern
     * @return bool Return true if successful; otherwise return false
     * @since 2.0.0
     */
    public function sendPurgeRequest($tagsPattern)
    {
        $socketAdapter = $this->socketAdapterFactory->create();
        $servers = $this->cacheServer->getUris();
        $headers = [self::HEADER_X_MAGENTO_TAGS_PATTERN => $tagsPattern];
        $socketAdapter->setOptions(['timeout' => 10]);
        foreach ($servers as $server) {
            $headers['Host'] = $server->getHost();
            try {
                $socketAdapter->connect($server->getHost(), $server->getPort());
                $socketAdapter->write(
                    'PURGE',
                    $server,
                    '1.1',
                    $headers
                );
                $socketAdapter->read();
                $socketAdapter->close();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage(), compact('server', 'tagsPattern'));
                return false;
            }
        }

        $this->logger->execute(compact('servers', 'tagsPattern'));
        return true;
    }
}
