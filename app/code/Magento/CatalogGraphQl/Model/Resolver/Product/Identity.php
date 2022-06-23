<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;

/**
 * Identity for resolved products
 */
class Identity implements IdentityInterface
{
    /** @var string */
    private $cacheTagProduct = Product::CACHE_TAG;
    private $cacheTagCategory = Category::CACHE_TAG;

    /**
     * Get product ids for cache tag
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        $categories = $resolvedData['categories'] ?? [];
        $items = $resolvedData['items'] ?? [];
        foreach ($categories as $category) {
            $ids[] = sprintf('%s_%s', $this->cacheTagCategory, $category);
        }
        if (!empty($categories)) {
            array_unshift($ids, $this->cacheTagCategory);
        }
        foreach ($items as $item) {
            $ids[] = sprintf('%s_%s', $this->cacheTagProduct, $item['entity_id']);
        }
        if (!empty($ids)) {
            array_unshift($ids, $this->cacheTagProduct);
        }

        return $ids;
    }
}
