<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Ui\Component\Report\Filters\Type;

/**
 * Class DateRange
 */
class DateRange extends \Magento\Ui\Component\Filters\Type\Date
{
    /**
     * Braintree date format
     *
     * @var string
     */
    protected static $dateFormat = 'Y-m-d\TH:i:00O';
}
