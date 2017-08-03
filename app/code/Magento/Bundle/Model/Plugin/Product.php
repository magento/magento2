<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Plugin;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\Product as CatalogProduct;

/**
 * Class \Magento\Bundle\Model\Plugin\Product
 *
 * @since 2.0.0
 */
class Product
{
    /**
     * @var Type
     * @since 2.0.0
     */
    private $type;

    /**
     * @param Type $type
     * @since 2.0.0
     */
    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    /**
     * @param CatalogProduct $product
     * @param array $identities
     * @return string[]
     * @since 2.0.0
     */
    public function afterGetIdentities(
        CatalogProduct $product,
        array $identities
    ) {
        foreach ($this->type->getParentIdsByChild($product->getEntityId()) as $parentId) {
            $identities[] = CatalogProduct::CACHE_TAG . '_' . $parentId;
        }
        return $identities;
    }
}
