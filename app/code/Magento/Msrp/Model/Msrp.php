<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;

/**
 * Class \Magento\Msrp\Model\Msrp
 *
 * @since 2.0.0
 */
class Msrp
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $mapApplyToProductType = null;

    /**
     * @var AttributeFactory
     * @since 2.0.0
     */
    protected $eavAttributeFactory;

    /**
     * @param AttributeFactory $eavAttributeFactory
     * @since 2.0.0
     */
    public function __construct(
        AttributeFactory $eavAttributeFactory
    ) {
        $this->eavAttributeFactory = $eavAttributeFactory;
    }

    /**
     * Check whether Msrp applied to product Product Type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @api
     * @since 2.0.0
     */
    public function canApplyToProduct($product)
    {
        if ($this->mapApplyToProductType === null) {
            /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            $attribute = $this->eavAttributeFactory->create()->loadByCode(Product::ENTITY, 'msrp');
            $this->mapApplyToProductType = $attribute->getApplyTo();
        }
        return in_array($product->getTypeId(), $this->mapApplyToProductType);
    }
}
