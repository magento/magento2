<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option\Type\File;

/**
 * Class \Magento\Catalog\Model\Product\Option\Type\File\ValidateFactory
 *
 */
class ValidateFactory
{
    /**
     * @return \Zend_Validate
     */
    public function create()
    {
        return new \Zend_Validate();
    }
}
