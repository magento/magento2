<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Type;

/**
 * Simple product type implementation
 * @since 2.0.0
 */
class Simple extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    /**
     * Delete data specific for Simple product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @since 2.0.0
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }
}
