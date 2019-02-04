<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CacheInvalidate\Model;

use Magento\Framework\Cache\InvalidateLogger;

/**
 * Class PurgeCache
 */
class PurgeCache
{
    const HEADER_X_MAGENTO_TAGS_PATTERN = 'X-Magento-Tags-Pattern';

    /**
     * @var \Magento\PageCache\Model\Cache\Server
     */
    protected $cacheServer;

    /**
     * @var \Magento\CacheInvalidate\Model\SocketFactory
     */
    protected $socketAdapterFactory;

    /**
     * @var InvalidateLogger
     */
    private $logger;

    /**
     * Batch size of the purge request.
     *
     * Based on default Varnish 4 http_req_hdr_len size minus a 512 bytes margin for method,
     * header name, line feeds etc.
     *
     * @see https://varnish-cache.org/docs/4.1/reference/varnishd.html
     *
     * @var int
     */
    private $requestSize = 7680;

    /**
     * Constructor
     *
     * @param \Magento\PageCache\Model\Cache\Server $cacheServer
     * @param \Magento\CacheInvalidate\Model\SocketFactory $socketAdapterFactory
     * @param InvalidateLogger $logger
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
     * Send curl purge request to invalidate cache by tags pattern
     *
     * @param string $tagsPattern
     * @return bool Return true if successful; otherwise return false
     */
    public function sendPurgeRequest($tagsPattern)
    {
        $successful = true;
        $socketAdapter = $this->socketAdapterFactory->create();
        $servers = $this->cacheServer->getUris();
        $socketAdapter->setOptions(['timeout' => 10]);

        $formattedTagsChunks = $this->splitTags($tagsPattern);
        foreach ($formattedTagsChunks as $formattedTagsChunk) {
            if (!$this->sendPurgeRequestToServers($socketAdapter, $servers, $formattedTagsChunk)) {
                $successful = false;
            }
        }

        return $successful;
    }

    /**
     * Split tags by batches
     *
     * @param string $tagsPattern
     * @return \Generator
     */
    private function splitTags($tagsPattern)
    {
        $tagsBatchSize = 0;
        $formattedTagsChunk = [];
        $formattedTags = explode('|', $tagsPattern);
        foreach ($formattedTags as $formattedTag) {
            if ($tagsBatchSize + strlen($formattedTag) > $this->requestSize - count($formattedTagsChunk) - 1) {
                yield implode('|', $formattedTagsChunk);
                $formattedTagsChunk = [];
                $tagsBatchSize = 0;
            }

            $tagsBatchSize += strlen($formattedTag);
            $formattedTagsChunk[] = $formattedTag;
        }
        if (!empty($formattedTagsChunk)) {
            yield implode('|', $formattedTagsChunk);
        }
    }

    /**
     * Send curl purge request to servers to invalidate cache by tags pattern
     *
     * @param \Zend\Http\Client\Adapter\Socket $socketAdapter
     * @param \Zend\Uri\Uri[] $servers
     * @param string $formattedTagsChunk
     * @return bool Return true if successful; otherwise return false
     */
    private function sendPurgeRequestToServers($socketAdapter, $servers, $formattedTagsChunk)
    {
        $headers = [self::HEADER_X_MAGENTO_TAGS_PATTERN => $formattedTagsChunk];
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
                $this->logger->critical($e->getMessage(), compact('server', 'formattedTagsChunk'));
                return false;
            }
        }
        $this->logger->execute(compact('servers', 'formattedTagsChunk'));
        return true;
    }
}
