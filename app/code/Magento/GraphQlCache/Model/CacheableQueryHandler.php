<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\App\RequestInterface;

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
     * @var IdentityResolverPool
     */
    private $identityResolverPool;

    /**
     * @param CacheableQuery $cacheableQuery
     * @param RequestInterface $request
     * @param IdentityResolverPool $identityResolverPool
     */
    public function __construct(
        CacheableQuery $cacheableQuery,
        RequestInterface $request,
        IdentityResolverPool $identityResolverPool
    ) {
        $this->cacheableQuery = $cacheableQuery;
        $this->request = $request;
        $this->identityResolverPool = $identityResolverPool;
    }

    /**
     * Set cache validity to the cacheableQuery after resolving any resolver or evaluating a promise in a query
     *
     * @param array $resolvedValue
     * @param Field $field
     * @return void
     */
    public function handleCacheFromResolverResponse(array $resolvedValue, Field $field) : void
    {
        $cache = $field->getCache();
        $cacheIdentityResolverClass = $cache['cacheIdentityResolver'] ?? '';
        $cacheable = $cache['cacheable'] ?? true;
        $cacheTag = $cache['cacheTag'] ?? null;

        $cacheTags = [];
        if ($cacheTag && $this->request->isGet()) {
            if (!empty($cacheIdentityResolverClass)) {
                $cacheIdentityResolver = $this->identityResolverPool->get($cacheIdentityResolverClass);
                $cacheTagIds = $cacheIdentityResolver->getIdentifiers($resolvedValue);
                if (!empty($cacheTagIds)) {
                    $cacheTags = array_map(
                        function ($id) use ($cacheTag) {
                            return $cacheTag . '_' . $id;
                        },
                        $cacheTagIds
                    );
                }
            } else {
                $cacheTags[] = $cacheTag;
            }

            $this->cacheableQuery->addCacheTags($cacheTags);
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
