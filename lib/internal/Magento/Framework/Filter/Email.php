<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Class \Magento\Framework\Filter\Email
 *
 * @since 2.0.0
 */
class Email implements \Zend_Filter_Interface
{
    /**
     * @param  mixed $value
     * @return mixed
     * @since 2.0.0
     */
    public function filter($value)
    {
        return $value;
    }
}
