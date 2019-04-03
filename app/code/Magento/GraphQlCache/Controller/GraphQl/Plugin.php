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
use Magento\Framework\App\State as AppState;
use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Plugin
 *
 * @package Magento\GraphQlCache\Controller\GraphQl
 */
class Plugin
{
    const CACHE_TTL = 'system/full_page_cache/ttl';

    /**
     * @var CacheInfo
     */
    private $cacheInfo;

    /**
     * @var AppState
     */
    private $state;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Plugin constructor.
     * @param CacheInfo $cacheInfo
     * @param AppState $state
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(CacheInfo $cacheInfo, AppState $state, ScopeConfigInterface $scopeConfig)
    {
        $this->cacheInfo = $cacheInfo;
        $this->state = $state;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Plugin for GraphQL Controller
     *
     * @param FrontControllerInterface $subject
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @return ResponseInterface|\Magento\Framework\Webapi\Response
     */
    public function afterDispatch(
        FrontControllerInterface $subject,
        /* \Magento\Framework\App\Response\Http */ $response,
        RequestInterface $request
    ) {
        $cacheTags = $this->cacheInfo->getCacheTags();
        $isCacheValid = $this->cacheInfo->isCacheable();
        $ttl = $this->getTtl();

        if (count($cacheTags) && $isCacheValid) {
            $response->setHeader('Pragma', 'cache', true);
            $response->setHeader('Cache-Control', 'max-age='.$ttl.', public, s-maxage='.$ttl.'', true);
            $response->setHeader('X-Magento-Tags', implode(',', $cacheTags), true);
        }

        if ($request->isGet() && $this->state->getMode() == AppState::MODE_DEVELOPER) {
            $response->setHeader('X-Magento-Debug', 1);
        }

        return $response;
    }

    /**
     * Return page lifetime
     *
     * @return int
     */
    private function getTtl()
    {
        return $this->scopeConfig->getValue(self::CACHE_TTL);
    }
}
