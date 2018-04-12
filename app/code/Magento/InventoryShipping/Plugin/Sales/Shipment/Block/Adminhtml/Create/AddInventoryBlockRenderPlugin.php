<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Plugin\Sales\Shipment\Block\Adminhtml\Create;

use Magento\Shipping\Block\Adminhtml\Create\Form;

/**
 * Render Inventory Block in adminhtml_order_shipment_new
 */
class AddInventoryBlockRenderPlugin
{
    /**
     * After get shipment Items
     * @param Form $subject
     * @param $result
     * @return string
     */
    public function afterGetItemsHtml(Form $subject, $result)
    {
        return $subject->getChildHtml('inventory_shipment') . $result;
    }
}
