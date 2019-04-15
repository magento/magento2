<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Framework\GraphQl\Query\IdentityResolverInterface;

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
