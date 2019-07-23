<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Email\Shipment;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order\Shipment\Track as TrackModel;
use Magento\Shipping\Helper\Data as ShippingHelper;

/**
 * Shipment track info for email
 */
class Track extends Template
{
    /**
     * @var ShippingHelper
     */
    private $helper;

    /**
     * @param Context $context
     * @param ShippingHelper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ShippingHelper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * Get Shipping tracking URL
     *
     * @param TrackModel $track
     * @return string
     */
    public function getTrackingUrl(TrackModel $track): string
    {
        return $this->helper->getTrackingPopupUrlBySalesModel($track);
    }

    /**
     * Get Shipping tracking URL escaped
     *
     * @param TrackModel $track
     * @return string
     */
    public function getTrackingUrlEscaped(TrackModel $track): string
    {
        return $this->escapeUrl($this->getTrackingUrl($track));
    }
}
