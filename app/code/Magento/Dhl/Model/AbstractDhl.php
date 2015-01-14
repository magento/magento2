<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Model;

use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;

abstract class AbstractDhl extends AbstractCarrierOnline
{
    /**
     * Response condition code for service is unavailable at the requested date
     */
    const CONDITION_CODE_SERVICE_DATE_UNAVAILABLE = 1003;

    /**
     * Count of days to look forward if day is not unavailable
     */
    const UNAVAILABLE_DATE_LOOK_FORWARD = 5;

    /**
     * Date format for request
     */
    const REQUEST_DATE_FORMAT = 'Y-m-d';

    /**
     * Get shipping date
     *
     * @return string
     */
    protected function _getShipDate()
    {
        return $this->_determineShippingDay($this->getConfigData('shipment_days'), date(self::REQUEST_DATE_FORMAT));
    }

    /**
     * Determine shipping day according to configuration settings
     *
     * @param string[] $shippingDays
     * @param string $date
     * @return string
     */
    protected function _determineShippingDay($shippingDays, $date)
    {
        if (empty($shippingDays)) {
            return $date;
        }

        $shippingDays = explode(',', $shippingDays);

        $i = -1;
        do {
            $i++;
            $weekday = date('D', strtotime("{$date} +{$i} day"));
        } while (!in_array($weekday, $shippingDays) && $i < 10);

        return date(self::REQUEST_DATE_FORMAT, strtotime("{$date} +{$i} day"));
    }
}
