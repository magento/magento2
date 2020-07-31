<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Ui\Component\Report\Filters\Type;

/**
 * Class DateRange
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
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
