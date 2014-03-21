<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
