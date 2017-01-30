<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

/**
 * Swatch Model
 */
class Swatch extends \Magento\Framework\Model\AbstractModel
{
    /** Constant for identifying attribute frontend type for textual swatch */
    const SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT = 'swatch_text';

    /** Constant for identifying attribute frontend type for visual swatch */
    const SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT = 'swatch_visual';

    /** Swatch input type key in array to retrieve the value */
    const SWATCH_INPUT_TYPE_KEY = 'swatch_input_type';

    /** Value for text swatch input type */
    const SWATCH_INPUT_TYPE_TEXT = 'text';

    /** Value for visual swatch input type */
    const SWATCH_INPUT_TYPE_VISUAL = 'visual';

    /** Value for dropdown input type */
    const SWATCH_INPUT_TYPE_DROPDOWN = 'dropdown';

    /** Constant for identifying textual swatch type */
    const SWATCH_TYPE_TEXTUAL = 0;

    /** Constant for identifying visual swatch type with color number value */
    const SWATCH_TYPE_VISUAL_COLOR = 1;

    /** Constant for identifying visual swatch type with color number value */
    const SWATCH_TYPE_VISUAL_IMAGE = 2;

    /** Constant for identifying empty swatch type */
    const SWATCH_TYPE_EMPTY = 3;

    /**
     * Name of swatch image
     */
    const SWATCH_IMAGE_NAME = 'swatch_image';

    /**
     * Name of swatch thumbnail
     */
    const SWATCH_THUMBNAIL_NAME = 'swatch_thumb';

    /**
     * Initialize resource model
     *
     * @codeCoverageIgnore
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Swatches\Model\ResourceModel\Swatch');
    }
}
