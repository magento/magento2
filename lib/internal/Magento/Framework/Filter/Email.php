<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
