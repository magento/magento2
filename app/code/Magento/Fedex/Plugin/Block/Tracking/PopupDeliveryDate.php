<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Fedex\Plugin\Block\Tracking;

use Magento\Shipping\Block\Tracking\Popup;
use Magento\Fedex\Model\Carrier;
use Magento\Shipping\Model\Tracking\Result\Status;

/**
 * Plugin to update delivery date value in case if Fedex used
 */
class PopupDeliveryDate
{
    /**
     * Show only date for expected delivery in case if Fedex is a carrier
     *
     * @param Popup $subject
     * @param string $result
     * @param string $date
     * @param string $time
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterFormatDeliveryDateTime(Popup $subject, $result, $date, $time)
    {
        if ($this->getCarrier($subject) === Carrier::CODE) {
            $result = $subject->formatDeliveryDate($date);
        }
        return $result;
    }

    /**
     * Retrieve carrier name from tracking info
     *
     * @param Popup $subject
     * @return string
     */
    private function getCarrier(Popup $subject): string
    {
        foreach ($subject->getTrackingInfo() as $trackingData) {
            foreach ($trackingData as $trackingInfo) {
                if ($trackingInfo instanceof Status) {
                    $carrier = $trackingInfo->getCarrier();
                    return $carrier;
                }
            }
        }
        return '';
    }
}
