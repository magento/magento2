<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Plugin\Sales\Block\Shipment;

use Magento\Shipping\Block\Adminhtml\Create;
use Magento\InventoryShippingAdminUi\Model\IsWebsiteInMultiSourceMode;

class BackButtonUrlOnNewShipmentPagePlugin
{
    /**
     * @var IsWebsiteInMultiSourceMode
     */
    private $isWebsiteInMultiSourceMode;

    /**
     * @param IsWebsiteInMultiSourceMode $isWebsiteInMultiSourceMode
     */
    public function __construct(
        IsWebsiteInMultiSourceMode $isWebsiteInMultiSourceMode
    ) {
        $this->isWebsiteInMultiSourceMode = $isWebsiteInMultiSourceMode;
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
        if ($this->isWebsiteInMultiSourceMode->execute($websiteId)) {
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
