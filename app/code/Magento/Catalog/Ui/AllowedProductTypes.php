<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui;


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
        $this->allowedProductTypes = array_merge($this->allowedProductTypes, $productTypes);
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
}
