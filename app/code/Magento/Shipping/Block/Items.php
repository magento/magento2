<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales order view items block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Shipping\Block;

/**
 * @api
 * @since 2.0.0
 */
class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @param object $shipment
     * @return string
     * @since 2.0.0
     */
    public function getPrintShipmentUrl($shipment)
    {
        return $this->getUrl('*/*/printShipment', ['shipment_id' => $shipment->getId()]);
    }

    /**
     * @param object $order
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCommentsHtml($shipment)
    {
        $html = '';
        $comments = $this->getChildBlock('shipment_comments');
        if ($comments) {
            $comments->setEntity($shipment)->setTitle(__('About Your Shipment'));
            $html = $comments->toHtml();
        }
        return $html;
    }
}
