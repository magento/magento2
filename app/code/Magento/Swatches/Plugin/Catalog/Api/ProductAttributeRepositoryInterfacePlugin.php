<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Plugin\Catalog\Api;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Swatches\Model\Swatch;

/**
 * Swatch attribute save preprocessor
 */
class ProductAttributeRepositoryInterfacePlugin
{
    /**
     * Preprocess swatch attributes
     *
     * @param ProductAttributeRepositoryInterface $subject
     * @param ProductAttributeInterface $attribute
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        ProductAttributeRepositoryInterface $subject,
        ProductAttributeInterface $attribute
    ) {
        switch ($attribute->getFrontendInput()) {
            case Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT:
                $attribute->setSwatchInputType(Swatch::SWATCH_INPUT_TYPE_VISUAL);
                $attribute->setFrontendInput('select');
                break;
            case Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT:
                $attribute->setSwatchInputType(Swatch::SWATCH_INPUT_TYPE_TEXT);
                $attribute->setUseProductImageForSwatch(0);
                $attribute->setFrontendInput('select');
                break;
        }
        return [$attribute];
    }
}
