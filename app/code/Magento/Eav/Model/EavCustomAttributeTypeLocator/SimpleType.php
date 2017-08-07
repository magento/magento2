<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\EavCustomAttributeTypeLocator;

use Magento\Framework\Reflection\TypeProcessor;

/**
 * Class to locate simple types for Eav custom attributes
 * @since 2.1.0
 */
class SimpleType
{
    /**
     * List of attributes, type of which cannot be identified reliably. We do not validate these attributes.
     *
     * @var string[]
     * @since 2.1.0
     */
    private $anyTypeAttributes = ['quantity_and_stock_status'];

    /**
     * Get attribute type based on its frontend input and backend type.
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @return string
     * @since 2.1.0
     */
    public function getType($attribute)
    {
        $arrayFrontendInputs = ['multiselect'];
        $frontendInput = $attribute->getFrontendInput();
        if (in_array($attribute->getAttributeCode(), $this->anyTypeAttributes)
            || in_array($frontendInput, $arrayFrontendInputs)
        ) {
            return TypeProcessor::NORMALIZED_ANY_TYPE;
        }

        $backendType = $attribute->getBackendType();
        $backendTypeMap = [
            'static' => TypeProcessor::NORMALIZED_ANY_TYPE,
            'int' => TypeProcessor::NORMALIZED_INT_TYPE,
            'text' => TypeProcessor::NORMALIZED_STRING_TYPE,
            'varchar' => TypeProcessor::NORMALIZED_STRING_TYPE,
            'datetime' => TypeProcessor::NORMALIZED_STRING_TYPE,
            'decimal' => TypeProcessor::NORMALIZED_DOUBLE_TYPE,
        ];
        return $backendTypeMap[$backendType];
    }
}
