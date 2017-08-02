<?php
/**
 * Cache configuration model. Provides cache configuration data to the application
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache;

use Magento\Framework\App\Request\Http as HttpRequest;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class \Magento\Framework\Cache\InvalidateLogger
 *
 * @since 2.0.0
 */
class InvalidateLogger
{
    /**
     * @var HttpRequest
     * @since 2.0.0
     */
    private $request;

    /**
     * @var Logger
     * @since 2.0.0
     */
    private $logger;

    /**
     * @param HttpRequest $request
     * @param Logger $logger
     * @since 2.0.0
     */
    public function __construct(HttpRequest $request, Logger $logger)
    {
        $this->request = $request;
        $this->logger = $logger;
    }

    /**
     * Logger invalidate cache
     * @param mixed $invalidateInfo
     * @return void
     * @since 2.0.0
     */
    public function execute($invalidateInfo)
    {
        $this->logger->debug('cache_invalidate: ', $this->makeParams($invalidateInfo));
    }

    /**
     * Make extra data to logger message
     * @param mixed $invalidateInfo
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function critical($message, $params)
    {
        $this->logger->critical($message, $this->makeParams($params));
    }
}
