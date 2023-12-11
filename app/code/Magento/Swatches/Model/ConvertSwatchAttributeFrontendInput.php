<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Model;

/**
 * Performs the conversion of the frontend input value for attribute data
 */
class ConvertSwatchAttributeFrontendInput
{
    /**
     * Performs the conversion of the frontend input value for attribute data
     *
     * @param array|null $data
     *
     * @return array|null
     */
    public function execute(?array $data): ?array
    {
        if (!isset($data['frontend_input'])) {
            return $data;
        }

        switch ($data['frontend_input']) {
            case 'swatch_visual':
                $data[Swatch::SWATCH_INPUT_TYPE_KEY] = Swatch::SWATCH_INPUT_TYPE_VISUAL;
                $data['frontend_input'] = 'select';
                break;
            case 'swatch_text':
                $data[Swatch::SWATCH_INPUT_TYPE_KEY] = Swatch::SWATCH_INPUT_TYPE_TEXT;
                $data['use_product_image_for_swatch'] = 0;
                $data['frontend_input'] = 'select';
                break;
            case 'select':
                $data[Swatch::SWATCH_INPUT_TYPE_KEY] = Swatch::SWATCH_INPUT_TYPE_DROPDOWN;
                $data['frontend_input'] = 'select';
                break;
        }

        return $data;
    }
}
