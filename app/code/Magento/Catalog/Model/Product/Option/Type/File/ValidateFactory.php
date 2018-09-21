<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type\File;

class ValidateFactory
{
    /**
     * @return \Zend_Validate
     */
    public function create()
    {
        return new ExistingValidate();
    }
}
