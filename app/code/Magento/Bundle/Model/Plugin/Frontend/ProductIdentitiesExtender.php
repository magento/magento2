<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Plugin\Frontend;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Product as CatalogProduct;

/**
 * Add child identities to product identities on storefront.
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
    private $cacheChildrenIds = [];

    /**
     * @param BundleType $type
     */
    public function __construct(BundleType $type)
    {
        $this->type = $type;
    }

    /**
     * Add child identities to product identities
     *
     * @param CatalogProduct $product
     * @param array $identities
     * @return array
     */
    public function afterGetIdentities(CatalogProduct $product, array $identities): array
    {
        if ($product->getTypeId() !== BundleType::TYPE_CODE) {
            return $identities;
        }
        foreach ($this->getChildrenIds($product->getEntityId()) as $childIds) {
            foreach ($childIds as $childId) {
                $identities[] = CatalogProduct::CACHE_TAG . '_' . $childId;
            }
        }

        return array_unique($identities);
    }

    /**
     * Get children ids with cache use
     *
     * @param mixed $entityId
     * @return array
     */
    private function getChildrenIds($entityId): array
    {
        if (!isset($this->cacheChildrenIds[$entityId])) {
            $this->cacheChildrenIds[$entityId] = $this->type->getChildrenIds($entityId);
        }

        return $this->cacheChildrenIds[$entityId];
    }
}
