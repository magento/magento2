<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Virtual product type implementation
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Type;

/**
 * Class \Magento\Catalog\Model\Product\Type\Virtual
 *
 * @since 2.0.0
 */
class Virtual extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    /**
     * Check is virtual product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @since 2.0.0
     */
    public function isVirtual($product)
    {
        return true;
    }

    /**
     * Check that product of this type has weight
     *
     * @return bool
     * @since 2.0.0
     */
    public function hasWeight()
    {
        return false;
    }

    /**
     * Delete data specific for Virtual product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @since 2.0.0
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }
}
