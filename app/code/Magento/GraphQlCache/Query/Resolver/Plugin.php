<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Query\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\Resolver\Context;
use Magento\GraphQlCache\Model\CacheableQuery;
use Magento\Framework\App\RequestInterface;

/**
 * Class Plugin
 *
 * @package Magento\GraphQlCache\Query\Resolver
 */
class Plugin
{
    /**
     * @var CacheableQuery
     */
    private $cacheableQuery;

    /**
     * @var Request
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
     * @inheritdoc
     *
     * @param ResolverInterface $subject
     * @param Object $resolvedValue
     * @param Field $field
     * @param Context $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     */
    public function afterResolve(
        ResolverInterface $subject,
        $resolvedValue,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $cache = $field->getCache();
        $cacheTag = isset($cache['cache_tag']) ? $cache['cache_tag'] : [];
        $cacheable = isset($cache['cacheable']) ? $cache['cacheable'] : true;
        if (!empty($cacheTag) && $this->request->isGet() && $cacheable) {
            $cacheTags = [$cacheTag];
            // Resolved value must have cache IDs defined
            $resolvedItemsIds = $this->extractResolvedItemsIds($resolvedValue);
            foreach ($resolvedItemsIds as $itemId) {
                $cacheTags[] = $cacheTag . '_' . $itemId;
            }
            $this->cacheableQuery->addCacheTags($cacheTags);
        }
        $this->setCacheValidity($cacheable);
        return $resolvedValue;
    }

    /**
     * Extract ids for resolved items
     *
     * @param Object $resolvedValue
     * @return array
     */
    private function extractResolvedItemsIds($resolvedValue)
    {
        if (isset($resolvedValue['ids']) && is_array($resolvedValue['ids'])) {
            return $resolvedValue['ids'];
        }
        if (isset($resolvedValue['items']) && is_array($resolvedValue['items'])) {
            return array_keys($resolvedValue['items']);
        }
        $ids = [];
        if (isset($resolvedValue['id'])) {
            $ids[] = $resolvedValue['id'];
            return $ids;
        }

        if (is_array($resolvedValue)) {
            foreach ($resolvedValue as $item) {
                if (isset($item['id'])) {
                    $ids[] = $item['id'];
                }
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
