<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Class Input type preprocessor.
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

    /**
     * Convert presentation to storable input type.
     *
     * @param array $data
     *
     * @return array
     */
    public function convertPresentationDataToInputType(array $data)
    {
        if ($data['frontend_input'] === 'texteditor') {
            $data['is_wysiwyg_enabled'] = 1;
            $data['frontend_input'] = 'textarea';
        } elseif ($data['frontend_input'] === 'textarea') {
            $data['is_wysiwyg_enabled'] = 0;
        }
        return $data;
    }
}
