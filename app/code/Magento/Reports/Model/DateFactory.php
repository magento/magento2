<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model;

class DateFactory
{
    /**
     * @param  string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface|array  $date    OPTIONAL Date value or value of date part to set
     *                                                 ,depending on $part. If null the actual time is set
     * @param  string                          $part    OPTIONAL Defines the input format of $date
     * @param  string|\Zend_Locale              $locale  OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\Date
     */
    public function create($date = null, $part = null, $locale = null)
    {
        return new \Magento\Framework\Stdlib\DateTime\Date($date, $part, $locale);
    }
}
