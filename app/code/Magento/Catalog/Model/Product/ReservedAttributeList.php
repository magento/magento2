<?php
/**
 * Reserved product attribute list
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product;

class ReservedAttributeList
{
    /**
     * @var string[]
     */
    protected $_reservedAttributes;

    /**
     * @param string $productModel
     * @param array $reservedAttributes
     * @param array $allowedAttributes
     */
    public function __construct($productModel, array $reservedAttributes = [], array $allowedAttributes = [])
    {
        $methods = get_class_methods($productModel);
        foreach ($methods as $method) {
            if (preg_match('/^get([A-Z]{1}.+)/', $method, $matches)) {
                $method = $matches[1];
                $tmp = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $method));
                $reservedAttributes[] = $tmp;
            }
        }
        $this->_reservedAttributes = array_diff($reservedAttributes, $allowedAttributes);
    }

    /**
     * Check whether attribute reserved or not
     *
     * @param \Magento\Catalog\Model\Entity\Attribute $attribute
     * @return boolean
     */
    public function isReservedAttribute($attribute)
    {
        return $attribute->getIsUserDefined() && in_array($attribute->getAttributeCode(), $this->_reservedAttributes);
    }
}
