<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\GraphQl;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\GraphQlCache\Model\CacheInfo;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Controller\ResultInterface;
use Magento\PageCache\Model\Config;

/**
 * Class Plugin
 */
class Plugin
{
    /**
     * @var CacheInfo
     */
    private $cacheInfo;

    /**
     * @var HttpResponse
     */
    private $response;

    /**
     * @var Config
     */
    private $config;

    /**
     * Plugin constructor.
     * @param CacheInfo $cacheInfo
     * @param Config $config
     * @param HttpResponse $response
     */
    public function __construct(CacheInfo $cacheInfo, Config $config, HttpResponse $response)
    {
        $this->cacheInfo = $cacheInfo;
        $this->config = $config;
        $this->response = $response;
    }

    /**
     * Plugin for GraphQL after dispatch to set tag and cache headers
     *
     * The $response doesn't have a set type because it's alternating between ResponseInterface and ResultInterface.
     *
     * @param FrontControllerInterface $subject
     * @param ResponseInterface | ResultInterface $response
     * @param RequestInterface $request
     * @return ResponseInterface | ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDispatch(
        FrontControllerInterface $subject,
        $response,
        RequestInterface $request
    ) {
        $cacheTags = $this->cacheInfo->getCacheTags();
        $isCacheValid = $this->cacheInfo->isCacheable();
        if (count($cacheTags)
            && $isCacheValid
            && $request->isGet()
            && $this->config->isEnabled()) {
            $this->response->setPublicHeaders($this->config->getTtl());
            $this->response->setHeader('X-Magento-Tags', implode(',', $cacheTags), true);
        }

        return $response;
    }
}
