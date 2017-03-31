<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class AllowedProductTypes contains product types on which some product type can be displayed
 */
class AllowedProductTypes
{
    /**
     * @var array
     */
    protected $allowedProductTypes = [];

    /**
     * @param array $productTypes
     */
    public function __construct(array $productTypes = [])
    {
        $this->allowedProductTypes = $productTypes;
    }

    /**
     * Get allowed product types
     *
     * @return array
     */
    public function getAllowedProductTypes()
    {
        return $this->allowedProductTypes;
    }

    /**
     * Check that product type is allowed
     *
     * @param ProductInterface $product
     * @return bool
     */
    public function isAllowedProductType(ProductInterface $product)
    {
        return in_array(
            $product->getTypeId(),
            $this->allowedProductTypes
        );
    }
}
