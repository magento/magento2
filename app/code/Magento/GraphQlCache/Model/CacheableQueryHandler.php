<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;

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
     * @param CacheableQuery $cacheableQuery
     * @param RequestInterface $request
     */
    public function __construct(CacheableQuery $cacheableQuery, RequestInterface $request)
    {
        $this->cacheableQuery = $cacheableQuery;
        $this->request = $request;
    }

    /**
     * Set cache validity to the cacheableQuery after resolving any resolver or evaluating a promise in a query
     *
     * @param mixed $resolvedValue
     * @param Field|null $field
     * @param ResolveInfo|null $info
     */
    public function handleCacheFromResolverResponse(array $resolvedValue, Field $field)
    {
        $cache = $field->getCache();
        $cacheTag = isset($cache['cache_tag']) ? $cache['cache_tag'] : [];
        $cacheable = isset($cache['cacheable']) ? $cache['cacheable'] : true;
        if (!empty($cacheTag) && $this->request->isGet() && $cacheable) {
            $cacheTags = [];
            // Resolved value must have cache IDs defined
            $resolvedItemsIds = $this->extractResolvedItemsIds($resolvedValue);
            if (!empty($resolvedItemsIds)) {
                $cacheTags = [$cacheTag];
            }
            foreach ($resolvedItemsIds as $itemId) {
                $cacheTags[] = $cacheTag . '_' . $itemId;
            }
            $this->cacheableQuery->addCacheTags($cacheTags);
        }
        $this->setCacheValidity($cacheable);
    }

    /**
     * Extract ids for resolved items
     *
     * @param mixed|Value $resolvedValue
     * @return array
     */
    private function extractResolvedItemsIds(array $resolvedValue) : array
    {
        $ids = [];
        if (isset($resolvedValue['ids']) && is_array($resolvedValue['ids'])) {
            return $resolvedValue['ids'];
        }
        if (isset($resolvedValue['items']) && is_array($resolvedValue['items'])) {
            return array_keys($resolvedValue['items']);
        }

        if (isset($resolvedValue['id'])) {
            $ids[] = $resolvedValue['id'];
            return $ids;
        }

        foreach ($resolvedValue as $item) {
            if (isset($item['id'])) {
                $ids[] = $item['id'];
            }
        }
        return $ids;
    }

    /**
     * Set cache validity for the graphql request
     *
     * @param bool $isValid
     */
    private function setCacheValidity(bool $isValid): void
    {
        $cacheValidity = $this->cacheableQuery->isCacheable() && $isValid;
        $this->cacheableQuery->setCacheValidity($cacheValidity);
    }
}