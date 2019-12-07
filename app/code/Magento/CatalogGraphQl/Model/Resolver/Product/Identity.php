<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved products
 */
class Identity implements IdentityInterface
{
    /** @var string */
    private $cacheTag = \Magento\Catalog\Model\Product::CACHE_TAG;

    /**
     * Get product ids for cache tag
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        $items = $resolvedData['items'] ?? [];
        foreach ($items as $item) {
            $ids[] = sprintf('%s_%s', $this->cacheTag, $item['entity_id']);
        }
        if (!empty($ids)) {
            array_unshift($ids, $this->cacheTag);
        }

        return $ids;
    }
}
