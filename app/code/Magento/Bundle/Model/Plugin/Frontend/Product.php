<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Plugin\Frontend;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product as CatalogProduct;

class Product
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @param Type $type
     */
    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    /**
     * Add child identities to product identities
     *
     * @param CatalogProduct $product
     * @param array $identities
     * @return string[]
     */
    public function afterGetIdentities(
        CatalogProduct $product,
        array $identities
    ) {
        foreach ($this->type->getChildrenIds($product->getEntityId()) as $optionId => $childIds) {
            foreach ($childIds as $childId) {
                $identities[] = CatalogProduct::CACHE_TAG . '_' . $childId;
            }
        }

        return array_unique($identities);
    }
}
