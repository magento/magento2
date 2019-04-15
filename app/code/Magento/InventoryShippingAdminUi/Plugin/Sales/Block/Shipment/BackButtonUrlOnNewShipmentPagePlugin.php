<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Plugin\Sales\Block\Shipment;

use Magento\Framework\App\ObjectManager;
use Magento\InventoryShippingAdminUi\Model\IsOrderSourceManageable;
use Magento\Shipping\Block\Adminhtml\Create;
use Magento\InventoryShippingAdminUi\Model\IsWebsiteInMultiSourceMode;

/**
 * Class BackButtonUrlOnNewShipmentPagePlugin
 */
class BackButtonUrlOnNewShipmentPagePlugin
{
    /**
     * @var IsWebsiteInMultiSourceMode
     */
    private $isWebsiteInMultiSourceMode;

    /**
     * @var IsOrderSourceManageable
     */
    private $isOrderSourceManageable;

    /**
     * @param IsWebsiteInMultiSourceMode $isWebsiteInMultiSourceMode
     * @param IsOrderSourceManageable $isOrderSourceManageable
     */
    public function __construct(
        IsWebsiteInMultiSourceMode $isWebsiteInMultiSourceMode,
        IsOrderSourceManageable $isOrderSourceManageable = null
    ) {
        $this->isWebsiteInMultiSourceMode = $isWebsiteInMultiSourceMode;
        $this->isOrderSourceManageable = $isOrderSourceManageable ??
            ObjectManager::getInstance()->get(IsOrderSourceManageable::class);
    }

    /**
     * Returns URL to Source Selection if source for order is manageable
     *
     * @param Create $subject
     * @param $result
     * @return string
     */
    public function afterGetBackUrl(Create $subject, $result)
    {
        $order = $subject->getShipment()->getOrder();
        if (empty($subject->getShipment()) || !$this->isOrderSourceManageable->execute($order)) {
            return $result;
        }

        $websiteId = (int)$order->getStore()->getWebsiteId();
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
