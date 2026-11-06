<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\CacheInvalidate\Model;

use Exception;
use Generator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Cache\InvalidateLogger;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\PageCache\Model\Cache\Server;
use Laminas\Uri\Uri;

/**
 * Invalidate external HTTP cache(s) based on tag pattern
 */
class PurgeCache
{
    public const HEADER_X_MAGENTO_TAGS_PATTERN = 'X-Magento-Tags-Pattern';

    /**
     * @var Server
     */
    protected $cacheServer;

    /**
     * @var SocketFactory
     * @deprecated Purge requests use the core HTTP client factory.
     * @see \Magento\Framework\HTTP\Client\CurlFactory
     */
    protected $socketAdapterFactory;

    /**
     * @var InvalidateLogger
     */
    private $logger;

    /**
     * Batch size of the purge request.
     *
     * Based on default Varnish 6 http_req_hdr_len size minus a 512 bytes margin for method,
     * header name, line feeds etc.
     *
     * @see https://varnish-cache.org/docs/6.0/reference/varnishd.html
     *
     * @var int
     */
    private $maxHeaderSize;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * Constructor
     *
     * @param Server $cacheServer
     * @param SocketFactory $socketAdapterFactory
     * @param InvalidateLogger $logger
     * @param int $maxHeaderSize
     * @param CurlFactory|null $curlFactory
     */
    public function __construct(
        Server $cacheServer,
        SocketFactory $socketAdapterFactory,
        InvalidateLogger $logger,
        int $maxHeaderSize = 7680,
        ?CurlFactory $curlFactory = null
    ) {
        $this->cacheServer = $cacheServer;
        $this->socketAdapterFactory = $socketAdapterFactory;
        $this->logger = $logger;
        $this->maxHeaderSize = $maxHeaderSize;
        $this->curlFactory = $curlFactory ?? ObjectManager::getInstance()->get(CurlFactory::class);
    }

    /**
     * Send curl purge request to invalidate cache by tags pattern
     *
     * @param array|string $tags
     * @return bool Return true if successful; otherwise return false
     */
    public function sendPurgeRequest($tags)
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }

        $successful = true;
        $servers = $this->cacheServer->getUris();

        $formattedTagsChunks = $this->chunkTags($tags);
        foreach ($formattedTagsChunks as $formattedTagsChunk) {
            if (!$this->sendPurgeRequestToServers($servers, $formattedTagsChunk)) {
                $successful = false;
            }
        }

        return $successful;
    }

    /**
     * Split tags into batches to suit Varnish max. header size
     *
     * @param array $tags
     * @return Generator
     */
    private function chunkTags(array $tags): Generator
    {
        $currentBatchSize = 0;
        $formattedTagsChunk = [];
        foreach ($tags as $formattedTag) {
            // Check if (currentBatchSize + length of next tag + number of pipe delimiters) would exceed header size.
            if ($currentBatchSize + strlen($formattedTag ?: '') + count($formattedTagsChunk) > $this->maxHeaderSize) {
                yield implode('|', $formattedTagsChunk);
                $formattedTagsChunk = [];
                $currentBatchSize = 0;
            }

            $currentBatchSize += strlen($formattedTag ?: '');
            $formattedTagsChunk[] = $formattedTag;
        }
        if (!empty($formattedTagsChunk)) {
            yield implode('|', $formattedTagsChunk);
        }
    }

    /**
     * Send curl purge request to servers to invalidate cache by tags pattern
     *
     * @param Uri[] $servers
     * @param string $formattedTagsChunk
     * @return bool Return true if successful; otherwise return false
     */
    private function sendPurgeRequestToServers(array $servers, string $formattedTagsChunk): bool
    {
        $headers = [self::HEADER_X_MAGENTO_TAGS_PATTERN => $formattedTagsChunk];
        $unresponsiveServerError = [];
        foreach ($servers as $server) {
            $headers['Host'] = $server->getHost();
            try {
                $client = $this->curlFactory->create();
                $client->setTimeout(10);
                $client->setHeaders($headers);
                $client->setOption(CURLOPT_CUSTOMREQUEST, 'PURGE');
                $client->setOption(CURLOPT_PROXY, '');
                $client->get((string)$server);
            } catch (Exception $e) {
                $unresponsiveServerError[] = "Cache host: " . $server->getHost() . ":" . $server->getPort() .
                    "resulted in error message: " . $e->getMessage();
            }
        }

        $errorCount = count($unresponsiveServerError);

        if ($errorCount > 0) {
            $loggerMessage = implode(" ", $unresponsiveServerError);

            if ($errorCount == count($servers)) {
                $this->logger->critical(
                    'No cache server(s) could be purged ' . $loggerMessage,
                    compact('servers', 'formattedTagsChunk')
                );
                return false;
            }

            $this->logger->warning(
                'Unresponsive cache server(s) hit' . $loggerMessage,
                compact('servers', 'formattedTagsChunk')
            );
        }

        $this->logger->execute(compact('servers', 'formattedTagsChunk'));
        return true;
    }
}
