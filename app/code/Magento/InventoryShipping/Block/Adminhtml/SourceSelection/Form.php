<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Block\Adminhtml\SourceSelection;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Registry;

class Form extends Widget
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->registry->registry('current_shipment');
    }

    /**
     * @return string
     */
    public function getItemsHtml()
    {
        return $this->getChildHtml('order_items');
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'adminhtml/order_shipment/new',
            [
                'order_id' => $this->getShipment()->getOrderId()
            ]
        );
    }

    /**
     * Retrieve websiteId for current order
     *
     * @return int
     */
    public function getWebsiteId()
    {
        if ($shipment = $this->getShipment()) {
            return $shipment->getOrder()->getStore()->getWebsiteId();
        }
        //TODO: ?
        return 1;
    }

    public function getSourcesList()
    {
        return $this->registry->registry('sources');
    }
}
