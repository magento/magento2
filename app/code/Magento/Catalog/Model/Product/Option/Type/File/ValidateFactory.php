<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type\File;

/**
 * Class ValidateFactory. Creates Validator with type "ExistingValidate"
 */
class ValidateFactory
{
    /**
     * Main factory method
     *
     * @return \Zend_Validate
     */
    public function create()
    {
        return new ExistingValidate();
    }
}
