<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

class Email implements \Zend_Filter_Interface
{
    /**
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        return $value;
    }
}
