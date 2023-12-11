<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Swatches\Model\Attribute\DataProvider;

use Magento\TestFramework\Eav\Model\Attribute\DataProvider\AbstractAttributeDataWithOptions;

/**
 * Base attribute data for swatch attributes.
 */
abstract class AbstractSwatchAttributeData extends AbstractAttributeDataWithOptions
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->defaultAttributePostData = array_replace(
            $this->defaultAttributePostData,
            [
                'update_product_preview_image' => 0,
                'use_product_image_for_swatch' => 0,
                'visual_swatch_validation' => '',
                'visual_swatch_validation_unique' => '',
                'text_swatch_validation' => '',
                'text_swatch_validation_unique' => '',
                'used_for_sort_by' => 0,
            ]
        );
        $this->defaultAttributePostData['swatch_input_type'] = 'text';
    }
}
