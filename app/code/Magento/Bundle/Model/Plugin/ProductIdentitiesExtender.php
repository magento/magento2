<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Plugin;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Product as CatalogProduct;

/**
 * Add parent identities to product identities.
 */
class ProductIdentitiesExtender
{
    /**
     * @var BundleType
     */
    private $type;

    /**
     * @var array
     */
    private $cacheParentIdsByChild = [];

    /**
     * @param BundleType $type
     */
    public function __construct(BundleType $type)
    {
        $this->type = $type;
    }

    /**
     * Add parent identities to product identities
     *
     * @param CatalogProduct $product
     * @param array $identities
     * @return string[]
     */
    public function afterGetIdentities(
        CatalogProduct $product,
        array $identities
    ) {
        if ($product->getTypeId() !== BundleType::TYPE_CODE) {
            return $identities;
        }
        foreach ($this->getParentIdsByChild($product->getEntityId()) as $parentId) {
            $identities[] = CatalogProduct::CACHE_TAG . '_' . $parentId;
        }

        return $identities;
    }

    /**
     * Get parent ids by child with cache use
     *
     * @param mixed $entityId
     * @return array
     */
    private function getParentIdsByChild($entityId): array
    {
        if (!isset($this->cacheParentIdsByChild[$entityId])) {
            $this->cacheParentIdsByChild[$entityId] = $this->type->getParentIdsByChild($entityId);
        }

        return $this->cacheParentIdsByChild[$entityId];
    }
}
