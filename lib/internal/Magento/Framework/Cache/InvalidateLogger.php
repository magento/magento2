<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Cache;

use Magento\Framework\App\Request\Http as HttpRequest;
use Psr\Log\LoggerInterface as Logger;

/**
 * Invalidate logger cache.
 */
class InvalidateLogger
{
    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param HttpRequest $request
     * @param Logger $logger
     */
    public function __construct(HttpRequest $request, Logger $logger)
    {
        $this->request = $request;
        $this->logger = $logger;
    }

    /**
     * Logger invalidate cache
     *
     * @param mixed $invalidateInfo
     * @return void
     */
    public function execute($invalidateInfo)
    {
        $context = $this->makeParams($invalidateInfo);
        if (isset($invalidateInfo['tags'], $invalidateInfo['mode'])) {
            if ($invalidateInfo['mode'] === 'all'
                && is_array($invalidateInfo['tags'])
                && empty($invalidateInfo['tags'])
            ) {
                // If we are sending a purge request to all cache storage capture the trace
                // This is not a usual flow, and likely a bug is causing a performance issue
                $context['trace'] = (string)(new \Exception('full purge of cache storage triggered'));
            }
        }
        $this->logger->debug('cache_invalidate: ', $context);
    }

    /**
     * Make extra data to logger message
     *
     * @param mixed $invalidateInfo
     * @return array
     */
    private function makeParams($invalidateInfo)
    {
        $method = $this->request->getMethod();
        $url = $this->request->getUriString();
        return compact('method', 'url', 'invalidateInfo');
    }

    /**
     * Log critical
     *
     * @param string $message
     * @param mixed $params
     * @return void
     */
    public function critical($message, $params)
    {
        $this->logger->critical($message, $this->makeParams($params));
    }

    /**
     * Log warning
     *
     * @param string $message
     * @param mixed $params
     * @return void
     */
    public function warning($message, $params)
    {
        $this->logger->warning($message, $this->makeParams($params));
    }
}
