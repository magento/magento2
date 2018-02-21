<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\EavCustomAttributeTypeLocator;

use Magento\Framework\Reflection\TypeProcessor;

/**
 * Class to locate simple types for Eav custom attributes
 */
class SimpleType
{
    /**
     * List of attributes, type of which cannot be identified reliably. We do not validate these attributes.
     * 
     * @var string[]
     */
    private $anyTypeAttributes = ['quantity_and_stock_status'];
    
    /**
     * Get attribute type based on its frontend input and backend type.
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @return string
     */
    public function getType($attribute)
    {
        if (in_array($attribute->getAttributeCode(), $this->anyTypeAttributes)) {
            return TypeProcessor::NORMALIZED_ANY_TYPE;
        }
        $frontendInput = $attribute->getFrontendInput();
        $backendType = $attribute->getBackendType();
        $backendTypeMap = [
            'static' => TypeProcessor::NORMALIZED_ANY_TYPE,
            'int' => TypeProcessor::NORMALIZED_INT_TYPE,
            'text' => TypeProcessor::NORMALIZED_STRING_TYPE,
            'varchar' => TypeProcessor::NORMALIZED_STRING_TYPE,
            'datetime' => TypeProcessor::NORMALIZED_STRING_TYPE,
            'decimal' => TypeProcessor::NORMALIZED_DOUBLE_TYPE,
        ];
        $arrayFrontendInputs = ['multiselect'];
        $type = $backendTypeMap[$backendType];
        if (in_array($frontendInput, $arrayFrontendInputs)) {
            $type .= '[]';
        }
        return $type;
    }
}
