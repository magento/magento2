<?php
/**
 * Product type transition manager
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;

/**
 * Class \Magento\Catalog\Model\Product\TypeTransitionManager
 *
 * @since 2.0.0
 */
class TypeTransitionManager
{
    /**
     * List of compatible product types
     *
     * @var array
     * @since 2.0.0
     */
    protected $compatibleTypes;

    /**
     * @var Edit\WeightResolver
     * @since 2.0.0
     */
    protected $weightResolver;

    /**
     * @param Edit\WeightResolver $weightResolver
     * @param array $compatibleTypes
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Edit\WeightResolver $weightResolver,
        array $compatibleTypes
    ) {
        $this->compatibleTypes = $compatibleTypes;
        $this->weightResolver = $weightResolver;
    }

    /**
     * Process given product and change its type if needed
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @since 2.0.0
     */
    public function processProduct(Product $product)
    {
        if (in_array($product->getTypeId(), $this->compatibleTypes)) {
            $product->setTypeInstance(null);
            $productTypeId = $this->weightResolver->resolveProductHasWeight($product)
                ? Type::TYPE_SIMPLE
                : Type::TYPE_VIRTUAL;
            $product->setTypeId($productTypeId);
        }
    }
}
