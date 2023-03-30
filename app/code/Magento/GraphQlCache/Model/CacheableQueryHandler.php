<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\GraphQlCache\Model\Resolver\IdentityPool;

/**
 * Handler for collecting tags on HTTP full page and built-in resolver caches.
 *
 * This class would be used to collect tags after each operation where we need to collect tags
 * usually after data is fetched or resolved.
 */
class CacheableQueryHandler
{
    /**
     * @var CacheableQuery
     */
    private $cacheableQuery;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var IdentityPool
     */
    private $identityPool;

    /**
     * @var array
     */
    private $fullPageIdentityToResolverIdentityClassMap;

    /**
     * @param CacheableQuery $cacheableQuery
     * @param RequestInterface $request
     * @param IdentityPool $identityPool
     * @param array $fullPageIdentityToResolverIdentityClassMap
     */
    public function __construct(
        CacheableQuery $cacheableQuery,
        RequestInterface $request,
        IdentityPool $identityPool,
        array $fullPageIdentityToResolverIdentityClassMap = []
    ) {
        $this->cacheableQuery = $cacheableQuery;
        $this->request = $request;
        $this->identityPool = $identityPool;
        $this->fullPageIdentityToResolverIdentityClassMap = $fullPageIdentityToResolverIdentityClassMap;
    }

    /**
     * Set HTTP full page cache validity on $cacheableQuery after resolving any resolver in a query
     *
     * @param array $resolvedValue
     * @param array $cacheAnnotation Eg: ['cacheable' => true, 'cacheTag' => 'someTag', cacheIdentity=>'\Mage\Class']
     * @return void
     */
    public function handleCacheFromResolverResponse(array $resolvedValue, array $cacheAnnotation) : void
    {
        $cacheable = $cacheAnnotation['cacheable'] ?? true;
        $cacheIdentityClass = $cacheAnnotation['cacheIdentity'] ?? '';

        if ($this->request instanceof Http && $this->request->isGet() && !empty($cacheIdentityClass)) {
            $cacheTags = $this->getCacheTagsByIdentityClassNameAndResolvedValue(
                $cacheIdentityClass,
                $resolvedValue
            );
            $this->cacheableQuery->addCacheTags($cacheTags);
        } else {
            $cacheable = false;
        }
        $this->setCacheValidity($cacheable);
    }

    /**
     * Get cache tags by class name and resolved value
     *
     * @param string $cacheIdentityClassName
     * @param array $resolvedValue
     * @param bool $isForBuiltInResolverCache - for HTTP full page cache if false
     * @return string[]
     */
    public function getCacheTagsByIdentityClassNameAndResolvedValue(
        string $cacheIdentityClassName,
        array $resolvedValue,
        bool $isForBuiltInResolverCache = false
    ): array {
        if ($isForBuiltInResolverCache) {
            $cacheIdentityClassName = $this->getResolverCacheIdentityClassName($cacheIdentityClassName);
        }

        $cacheIdentity = $this->getCacheIdentityByClassName($cacheIdentityClassName);

        return $cacheIdentity->getIdentities($resolvedValue);
    }

    /**
     * Get resolver cache identity class name if present.  If not, use original $cacheIdentityClassName
     *
     * @param string $cacheIdentityClassName
     * @return string
     */
    private function getResolverCacheIdentityClassName(string $cacheIdentityClassName): string
    {
        if (isset($this->fullPageIdentityToResolverIdentityClassMap[$cacheIdentityClassName])) {
            $cacheIdentityClassName = $this->fullPageIdentityToResolverIdentityClassMap[$cacheIdentityClassName];
        }

        return $cacheIdentityClassName;
    }

    /**
     * Get cache identity object by class name
     *
     * @param string $cacheIdentityClassName
     * @return IdentityInterface
     */
    private function getCacheIdentityByClassName(string $cacheIdentityClassName): IdentityInterface
    {
        return $this->identityPool->get($cacheIdentityClassName);
    }

    /**
     * Set HTTP full page cache validity for the graphql request
     *
     * @param bool $isValid
     * @return void
     */
    private function setCacheValidity(bool $isValid): void
    {
        $cacheValidity = $this->cacheableQuery->isCacheable() && $isValid;
        $this->cacheableQuery->setCacheValidity($cacheValidity);
    }
}
