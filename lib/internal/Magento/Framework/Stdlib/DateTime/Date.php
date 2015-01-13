<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime;

class Date extends \Zend_Date implements \Magento\Framework\Stdlib\DateTime\DateInterface
{
    /**
     * Generates the standard date object, could be a unix timestamp, localized date,
     * string, integer, array and so on. Also parts of dates or time are supported
     * Always set the default timezone: http://php.net/date_default_timezone_set
     * For example, in your bootstrap: date_default_timezone_set('America/Los_Angeles');
     * For detailed instructions please look in the docu.
     *
     * @param  string|integer|\Magento\Framework\Stdlib\DateTime\DateInterface|array $date OPTIONAL Date value or value
     *         of date part to set, depending on $part. If null the actual time is set
     * @param  string $part OPTIONAL Defines the input format of $date
     * @param  string|\Magento\Framework\Stdlib\DateTime\DateInterface $locale OPTIONAL Locale for parsing input
     * @return \Magento\Framework\Stdlib\DateTime\DateInterface
     * @throws Zend_Date_Exception
     */
    public function __construct($date = null, $part = null, $locale = null)
    {
        parent::__construct($date, $part, $locale);
    }
}
