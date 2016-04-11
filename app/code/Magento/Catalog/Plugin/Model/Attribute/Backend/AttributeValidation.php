<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Plugin\Model\Attribute\Backend;

class AttributeValidation
{
    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\DataObject $product
     * @return bool
     */
    public function aroundValidate(
        \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend $subject,
        \Closure $proceed,
        \Magento\Framework\DataObject $product
    ) {
        $attrCode = $subject->getAttribute()->getAttributeCode();
        // Null is meaning "no value" which should be overridden by value from default scope
        if (array_key_exists($attrCode, $product->getData()) && $product->getData($attrCode) === null) {
            return true;
        }
        return $proceed($product);
    }
}
