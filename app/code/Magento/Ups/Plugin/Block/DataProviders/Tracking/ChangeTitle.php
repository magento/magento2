<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ups\Plugin\Block\DataProviders\Tracking;

use Magento\Ups\Model\Carrier;
use Magento\Shipping\Model\Tracking\Result\Status;
use Magento\Shipping\Block\DataProviders\Tracking\DeliveryDateTitle as Subject;

/**
 * Plugin to change the "Delivery on" title to a customized value for UPS
 */
class ChangeTitle
{
    /**
     * Modify title only when UPS is used as carrier
     *
     * @param Subject $subject
     * @param \Magento\Framework\Phrase|string $result
     * @param Status $trackingStatus
     * @return \Magento\Framework\Phrase|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetTitle(Subject $subject, $result, Status $trackingStatus)
    {
        if ($trackingStatus->getCarrier() === Carrier::CODE) {
            $result = __('Status Updated On:');
        }
        return $result;
    }
}
