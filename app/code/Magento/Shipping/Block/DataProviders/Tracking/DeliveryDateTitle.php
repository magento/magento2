<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Block\DataProviders\Tracking;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Shipping\Model\Tracking\Result\Status;

/**
 * Extension point to provide ability to change tracking details titles
 */
class DeliveryDateTitle implements ArgumentInterface
{
    /**
     * Returns Title in case if carrier defined
     *
     * @param Status $trackingStatus
     * @return \Magento\Framework\Phrase|string
     */
    public function getTitle(Status $trackingStatus)
    {
        return $trackingStatus->getCarrier() ? __('Delivered on:') : '';
    }
}
