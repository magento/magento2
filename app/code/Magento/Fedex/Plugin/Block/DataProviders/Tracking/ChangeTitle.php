<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Fedex\Plugin\Block\DataProviders\Tracking;

use Magento\Fedex\Model\Carrier;
use Magento\Shipping\Model\Tracking\Result\Status;
use Magento\Shipping\Block\DataProviders\Tracking\DeliveryDateTitle as Subject;

/**
 * Plugin to change delivery date title with FedEx customized value
 */
class ChangeTitle
{
    /**
     * Title modification in case if FedEx used as carrier
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
            $result = __('Expected Delivery:');
        }
        return $result;
    }
}
