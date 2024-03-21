<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Plugin;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\GraphQl\Controller\HttpRequestProcessor;
use Magento\GraphQlCache\Model\CacheableQuery;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\PageCache\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Plugin for handling controller after controller tags and pre-controller validation.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var HttpRequestProcessor
     */
    private $requestProcessor;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CacheIdCalculator
     */
    private $cacheIdCalculator;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param CacheableQuery $cacheableQuery
     * @param CacheIdCalculator $cacheIdCalculator
     * @param Config $config
     * @param LoggerInterface $logger
     * @param HttpRequestProcessor $requestProcessor
     * @param Registry $registry
     */
    public function __construct(
        CacheableQuery $cacheableQuery,
        CacheIdCalculator $cacheIdCalculator,
        Config $config,
        LoggerInterface $logger,
        HttpRequestProcessor $requestProcessor,
        Registry $registry
    ) {
        $this->cacheableQuery = $cacheableQuery;
        $this->cacheIdCalculator = $cacheIdCalculator;
        $this->config = $config;
        $this->logger = $logger;
        $this->requestProcessor = $requestProcessor;
        $this->registry = $registry;
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
    ): void {
        try {
            $this->requestProcessor->validateRequest($request);
        } catch (\Exception $error) {
            $this->logger->critical($error->getMessage());
        }
        /** @var \Magento\Framework\App\Request\Http $request */
        $this->requestProcessor->processHeaders($request);
        $this->request = $request;
    }

    /**
     * Plugin for GraphQL after render from dispatch to set tag and cache headers
     *
     * @param ResultInterface $subject
     * @param ResultInterface $result
     * @param ResponseHttp $response
     * @return ResultInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRenderResult(ResultInterface $subject, ResultInterface $result, ResponseHttp $response)
    {
        if (!$this->config->isEnabled() || $this->request && str_contains($this->request->getContent(), 'mutation')) {
            $response->setNoCacheHeaders();
            return $result;
        }

        /** @see \Magento\Framework\App\Http::launch */
        /** @see \Magento\PageCache\Model\Controller\Result\BuiltinPlugin::afterRenderResult */
        $this->registry->register('use_page_cache_plugin', true, true);

        $cacheId = $this->cacheIdCalculator->getCacheId();
        if ($cacheId) {
            $response->setHeader(CacheIdCalculator::CACHE_ID_HEADER, $cacheId, true);
        }
        if (!$this->cacheableQuery->shouldPopulateCacheHeadersWithTags()) {
            $response->setNoCacheHeaders();
            return $result;
        }

        $response->setPublicHeaders($this->config->getTtl());
        $response->setHeader('X-Magento-Tags', implode(',', $this->cacheableQuery->getCacheTags()), true);

        return $result;
    }
}
