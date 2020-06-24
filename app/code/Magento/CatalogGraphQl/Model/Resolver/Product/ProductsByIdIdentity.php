<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for resolved products by IDs
 */
class ProductsByIdIdentity implements IdentityInterface
{
    /**
     * @var string
     */
    private $cacheTag = Product::CACHE_TAG;

    /**
     * Get product ids for cache tag
     *
     * @param array $resolvedData
     * @return array
     */
    public function getIdentities(array $resolvedData): array
    {
        return empty($resolvedData['ids']) ?
            [] : [$this->cacheTag, implode('_', $resolvedData['ids'])];
    }
}
