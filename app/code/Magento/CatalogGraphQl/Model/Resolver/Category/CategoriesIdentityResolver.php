<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Framework\GraphQl\Query\IdentityResolverInterface;

/**
 * Identity for multiple resolved categories
 */
class CategoriesIdentityResolver implements IdentityResolverInterface
{
    /**
     * Get category IDs from resolved data
     *
     * @param array $resolvedData
     * @return array
     */
    public function getIdentifiers(array $resolvedData): array
    {
        $ids = [];
        if (!empty($resolvedData)) {
            foreach ($resolvedData as $category) {
                $ids[] = $category['id'];
            }
        }
        return $ids;
    }
}
