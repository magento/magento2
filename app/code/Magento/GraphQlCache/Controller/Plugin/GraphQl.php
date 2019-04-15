<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Plugin;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\GraphQlCache\Model\CacheableQuery;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Controller\ResultInterface;
use Magento\PageCache\Model\Config;
use Magento\GraphQl\Controller\HttpRequestProcessor;

/**
 * Class Plugin
 */
class GraphQl
{
    /**
     * @var CacheableQuery
     */
    private $cacheableQuery;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var HttpResponse
     */
    private $response;

    /**
     * @var HttpRequestProcessor
     */
    private $requestProcessor;

    /**
     * @param CacheableQuery $cacheableQuery
     * @param Config $config
     * @param HttpResponse $response
     * @param HttpRequestProcessor $requestProcessor
     */
    public function __construct(
        CacheableQuery $cacheableQuery,
        Config $config,
        HttpResponse $response,
        HttpRequestProcessor $requestProcessor
    ) {
        $this->cacheableQuery = $cacheableQuery;
        $this->config = $config;
        $this->response = $response;
        $this->requestProcessor = $requestProcessor;
    }

    /**
     * Process graphql headers
     *
     * @param FrontControllerInterface $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(
        FrontControllerInterface $subject,
        RequestInterface $request
    ) {
        /** @var \Magento\Framework\App\Request\Http $request */
        $this->requestProcessor->processHeaders($request);
    }

    /**
     * Plugin for GraphQL after dispatch to set tag and cache headers
     *
     * The $response doesn't have a set type because it's alternating between ResponseInterface and ResultInterface
     * depending if it comes from builtin cache or the dispatch.
     *
     * @param FrontControllerInterface $subject
     * @param ResponseInterface|ResultInterface $response
     * @param RequestInterface $request
     * @return ResponseInterface|ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDispatch(
        FrontControllerInterface $subject,
        $response,
        RequestInterface $request
    ) {
        $sendNoCacheHeaders = false;
        if ($this->config->isEnabled() && $request->isGet()) {
            if ($this->cacheableQuery->shouldPopulateCacheHeadersWithTags()) {
                $this->response->setPublicHeaders($this->config->getTtl());
                $this->response->setHeader('X-Magento-Tags', implode(',', $this->cacheableQuery->getCacheTags()), true);
            } else {
                $sendNoCacheHeaders = true;
            }
        } else {
            $sendNoCacheHeaders = true;
        }

        if ($sendNoCacheHeaders) {
            $this->response->setNoCacheHeaders();
        }

        return $response;
    }
}
