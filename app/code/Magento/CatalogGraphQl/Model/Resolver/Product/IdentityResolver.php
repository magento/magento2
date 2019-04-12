<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\GraphQl\Model\IdentityResolverInterface;

/**
 * Identity for resolved products
 */
class IdentityResolver implements IdentityResolverInterface
{
    /**
     * Get product ids for cache tag
     *
     * @param array $resolvedData
     * @return array
     */
    public function getIdentifiers(array $resolvedData) : array
    {
        $ids = [];
        $items = $resolvedData['items'] ?? [];
        foreach ($items as $item) {
            $ids[] = $item['entity_id'];
        }

        return $ids;
    }
}
