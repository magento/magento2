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
     * @param mixed $invalidateInfo
     * @return void
     */
    public function execute($invalidateInfo)
    {
        $this->logger->debug('cache_invalidate: ', $this->makeParams($invalidateInfo));
    }

    /**
     * Make extra data to logger message
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
}
