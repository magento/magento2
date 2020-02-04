<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\DataProviders\Email\Shipment;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Shipping\Helper\Data as ShippingHelper;

/**
 * Shipment track info for email
 */
class TrackingUrl implements ArgumentInterface
{
    /**
     * @var ShippingHelper
     */
    private $helper;

    /**
     * @param ShippingHelper $helper
     */
    public function __construct(ShippingHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Get Shipping tracking URL
     *
     * @param Track $track
     * @return string
     */
    public function getUrl(Track $track): string
    {
        return $this->helper->getTrackingPopupUrlBySalesModel($track);
    }
}
