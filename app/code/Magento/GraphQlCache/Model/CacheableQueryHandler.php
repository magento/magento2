<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\Http;
use Magento\GraphQlCache\Model\Resolver\IdentityPool;

/**
 * Handler of collecting tagging on cache.
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
     * @param CacheableQuery $cacheableQuery
     * @param RequestInterface $request
     * @param IdentityPool $identityPool
     */
    public function __construct(
        CacheableQuery $cacheableQuery,
        RequestInterface $request,
        IdentityPool $identityPool
    ) {
        $this->cacheableQuery = $cacheableQuery;
        $this->request = $request;
        $this->identityPool = $identityPool;
    }

    /**
     * Set cache validity to the cacheableQuery after resolving any resolver or evaluating a promise in a query
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
            $cacheIdentity = $this->identityPool->get($cacheIdentityClass);
            $cacheTags = $cacheIdentity->getIdentities($resolvedValue);
            $this->cacheableQuery->addCacheTags($cacheTags);
        } else {
            $cacheable = false;
        }
        $this->setCacheValidity($cacheable);
    }

    /**
     * Set cache validity for the graphql request
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
