<?php
/**
 * Reserved product attribute list
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function __construct($productModel, array $reservedAttributes = array(), array $allowedAttributes = array())
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
