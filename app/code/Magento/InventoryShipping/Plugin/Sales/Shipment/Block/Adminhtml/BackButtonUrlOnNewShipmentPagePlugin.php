<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Plugin\Sales\Shipment\Block\Adminhtml;

use Magento\Shipping\Block\Adminhtml\Create;
use Magento\InventoryShipping\Model\IsMultiSourceMode;

class BackButtonUrlOnNewShipmentPagePlugin
{
    /**
     * @var IsMultiSourceMode
     */
    private $isMultiSourceMode;

    /**
     * @param IsMultiSourceMode $isMultiSourceMode
     */
    public function __construct(
        IsMultiSourceMode $isMultiSourceMode
    ) {
        $this->isMultiSourceMode = $isMultiSourceMode;
    }

    /**
     * @param Create $subject
     * @param $result
     * @return string
     */
    public function afterGetBackUrl(Create $subject, $result)
    {
        if (empty($subject->getShipment())) {
            return $result;
        }

        $websiteId = (int)$subject->getShipment()->getOrder()->getStore()->getWebsiteId();
        if ($this->isMultiSourceMode->execute($websiteId)) {
            return $subject->getUrl(
                'inventoryshipping/SourceSelection/index',
                [
                    'order_id' => $subject->getShipment() ? $subject->getShipment()->getOrderId() : null
                ]
            );
        }

        return $result;
    }
}
