<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales order view items block
 */
namespace Magento\Shipping\Block;

/**
 * Shipping Items Block
 *
 * @api
 * @since 100.0.2
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Get Print Shipment Url
     *
     * @param object $shipment
     * @return string
     */
    public function getPrintShipmentUrl($shipment)
    {
        return $this->getUrl('*/*/printShipment', ['shipment_id' => $shipment->getId()]);
    }

    /**
     * Get Print All Shipments Url
     *
     * @param object $order
     * @return string
     */
    public function getPrintAllShipmentsUrl($order)
    {
        return $this->getUrl('*/*/printShipment', ['order_id' => $order->getId()]);
    }

    /**
     * Get html of shipment comments block
     *
     * @param   \Magento\Sales\Model\Order\Shipment $shipment
     * @return  string
     */
    public function getCommentsHtml($shipment)
    {
        $html = '';
        $comments = $this->getChildBlock('shipment_comments');
        if ($comments) {
            $comments->setEntity($shipment)->setTitle($this->escapeHtmlAttr(__('About Your Shipment')));
            $html = $comments->toHtml();
        }
        return $html;
    }
}
