<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Default implementation of the AttributeMetadataBuilderInterface
 */
class AttributeMetadataBuilder extends AbstractSimpleObjectBuilder implements AttributeMetadataBuilderInterface
{
    const ATTRIBUTE_CODE = 'attribute_code';

    /**
     * Set attribute code
     *
     * @param  string $attributeCode
     * @return $this
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->_set(self::ATTRIBUTE_CODE, $attributeCode);
    }
}
