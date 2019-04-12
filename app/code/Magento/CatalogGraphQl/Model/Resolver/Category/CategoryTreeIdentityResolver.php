<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\GraphQl\Model\IdentityResolverInterface;

/**
 * Identity for resolved category
 */
class CategoryTreeIdentityResolver implements IdentityResolverInterface
{
    /**
     * Get category ID from resolved data
     *
     * @param array $resolvedData
     * @return array
     */
    public function getIdentifiers(array $resolvedData): array
    {
        return empty($resolvedData['id']) ? [] : [$resolvedData['id']];
    }
}
