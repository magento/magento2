<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as AttributeResource;

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
     * @return mixed|string
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
     * Convert presentation to stored input type.
     *
     * @param Attribute $attributeResource
     *
     * @return $this
     */
    public function convertInputTypeFromPresentation(AttributeResource $attributeResource)
    {
        if ($attributeResource->getFrontendInput() == 'texteditor') {
            $attributeResource->setFrontendInput('textarea');
            $attributeResource->setIsWysiwygEnabled(true);
            $attributeResource->setIsHtmlAllowedOnFront(1);
        } else if ($attributeResource->getFrontendInput() == 'textarea') {
            $attributeResource->setIsWysiwygEnabled(false);
        }
        return $this;
    }
}
