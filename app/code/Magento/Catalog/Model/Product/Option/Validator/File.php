<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Validator;

use Magento\Catalog\Model\Product\Option;

/**
 * Class \Magento\Catalog\Model\Product\Option\Validator\File
 *
 * @since 2.0.0
 */
class File extends DefaultValidator
{
    /**
     * Validate option type fields
     *
     * @param Option $option
     * @return bool
     * @since 2.0.0
     */
    protected function validateOptionValue(Option $option)
    {
        $result = parent::validateOptionValue($option);
        return $result && !$this->isNegative($option->getImageSizeX()) && !$this->isNegative($option->getImageSizeY());
    }
}
