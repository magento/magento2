<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Model\Product\Option\Type\File;

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
