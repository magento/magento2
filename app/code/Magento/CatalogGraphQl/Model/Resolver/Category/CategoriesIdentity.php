<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity for multiple resolved categories
 */
class CategoriesIdentity implements IdentityInterface
{
    /** @var string */
    private $cacheTag = \Magento\Catalog\Model\Category::CACHE_TAG;

    /**
     * Get category IDs from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        if (!empty($resolvedData)) {
            foreach ($resolvedData as $category) {
                $ids[] = sprintf('%s_%s', $this->cacheTag, $category['id']);
            }
            if (!empty($ids)) {
                array_unshift($ids, $this->cacheTag);
            }
        }
        return $ids;
    }
}
