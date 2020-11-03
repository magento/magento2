<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Plugin;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Product as CatalogProduct;

class Product
{
    /**
     * @var BundleType
     */
    private $type;

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
        foreach ($this->type->getParentIdsByChild($product->getEntityId()) as $parentId) {
            $identities[] = CatalogProduct::CACHE_TAG . '_' . $parentId;
        }
        return $identities;
    }
}
