<?php
/**
 * Date filter. Converts date from localized to internal format.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime\Filter;

class Date implements \Zend_Filter_Interface
{
    /**
     * Convert date from localized to internal format
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        $value = new \DateTime($value, new \DateTimeZone('UTC'));
        return $value->format('Y-m-d H:i:s');
    }
}
