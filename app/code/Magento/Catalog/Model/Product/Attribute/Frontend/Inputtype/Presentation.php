<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Input type to presentation type converter.
 *
 * @package Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype
 */
class Presentation
{
    /**
     * Get input type for presentation layer from stored input type.
     *
     * @param Attribute $attribute
     * @return string
     */
    public function getPresentationInputType(Attribute $attribute)
    {
        $inputType = $attribute->getFrontendInput();
        if ($inputType == 'textarea' && $attribute->getIsWysiwygEnabled()) {
            return 'texteditor';
        }
        return $inputType;
    }
}
