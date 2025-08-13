<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

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
     * @return string|null
     */
    public function getPresentationInputType(Attribute $attribute) :?string
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
    public function convertPresentationDataToInputType(array $data) : array
    {
        if (isset($data['frontend_input']) && $data['frontend_input'] === 'texteditor') {
            $data['is_wysiwyg_enabled'] = 1;
            $data['frontend_input'] = 'textarea';
        } elseif (isset($data['frontend_input']) && $data['frontend_input'] === 'textarea') {
            $data['is_wysiwyg_enabled'] = 0;
        }
        return $data;
    }
}
