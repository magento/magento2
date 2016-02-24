<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Plugin\Model\Attribute\Backend;

class AttributeValidation
{
    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\DataObject $attribute
     * @return bool
     */
    public function aroundValidate(
        \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend $subject,
        \Closure $proceed,
        \Magento\Framework\DataObject $attribute
    ) {
        $useDefault = $attribute->getUseDefault();
        $attrCode = $subject->getAttribute()->getAttributeCode();
        if ($useDefault && isset($useDefault[$attrCode])) {
            return true;
        }
        return $proceed($attribute);
    }
}
