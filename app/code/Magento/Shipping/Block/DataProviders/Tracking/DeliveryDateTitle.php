<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Block\DataProviders\Tracking;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Shipping\Model\Tracking\Result\Status;

/**
 * Extension point to provide ability to change tracking details titles
 */
class DeliveryDateTitle implements ArgumentInterface
{
    /**
     * @param Status $trackingStatus
     * @return string
     */
    public function getTitle(Status $trackingStatus): string
    {
        return $trackingStatus->getCarrier() ? __('Delivered on:') : '';
    }
}
