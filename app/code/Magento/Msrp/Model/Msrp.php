<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;

/**
 * @api
 */
class Msrp
{
    /**
     * @var array
     */
    protected $mapApplyToProductType = null;

    /**
     * @var AttributeFactory
     */
    protected $eavAttributeFactory;

    /**
     * @param AttributeFactory $eavAttributeFactory
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
