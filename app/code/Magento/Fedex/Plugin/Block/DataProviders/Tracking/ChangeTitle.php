<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * @param Subject $subject
     * @param string $result
     * @param Status $trackingStatus
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetTitle(Subject $subject, string $result, Status $trackingStatus): string
    {
        if ($trackingStatus->getCarrier() === Carrier::CODE) {
            $result = __('Expected Delivery:');
        }
        return $result;
    }
}
