<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Validator;

use Magento\Catalog\Model\Product\Option;

class Text extends DefaultValidator
{
    /**
     * Validate option type fields
     *
     * @param Option $option
     * @return bool
     */
    protected function validateOptionValue(Option $option)
    {
        $result = parent::validateOptionValue($option);
        return $result && !$this->isNegative($option->getMaxCharacters());
    }
}
