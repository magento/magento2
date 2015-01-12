<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order\PrintOrder;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Sales order details block
 */
class Shipment extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Tracks for Shippings
     *
     * @var array
     */
    protected $_tracks = [];

    /**
     * Order shipments collection
     *
     * @var array|\Magento\Sales\Model\Resource\Order\Shipment\Collection
     */
    protected $_shipmentsCollection;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentHelper,
        array $data = []
    ) {
        $this->_paymentHelper = $paymentHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Load all tracks and save it to local cache by shipments
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $tracksCollection = $this->getOrder()->getTracksCollection();

        foreach ($tracksCollection->getItems() as $track) {
            $shipmentId = $track->getParentId();
            $this->_tracks[$shipmentId][] = $track;
        }

        $shipment = $this->_coreRegistry->registry('current_shipment');
        if ($shipment) {
            $this->_shipmentsCollection = [$shipment];
        } else {
            $this->_shipmentsCollection = $this->getOrder()->getShipmentsCollection();
        }

        return parent::_beforeToHtml();
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Order # %1', $this->getOrder()->getRealOrderId()));
        $infoBlock = $this->_paymentHelper->getInfoBlock($this->getOrder()->getPayment(), $this->getLayout());
        $this->setChild('payment_info', $infoBlock);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/history');
    }

    /**
     * @return string
     */
    public function getPrintUrl()
    {
        return $this->getUrl('*/*/print');
    }

    /**
     * @return string
     */
    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * @return array|null
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * @return array|null
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    /**
     * @param AbstractBlock $renderer
     * @return $this
     */
    protected function _prepareItem(AbstractBlock $renderer)
    {
        $renderer->setPrintStatus(true);

        return parent::_prepareItem($renderer);
    }

    /**
     * Retrieve order shipments collection
     *
     * @return array|\Magento\Sales\Model\Resource\Order\Shipment\Collection
     */
    public function getShipmentsCollection()
    {
        return $this->_shipmentsCollection;
    }

    /**
     * Getter for order tracking numbers collection per shipment
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return array
     */
    public function getShipmentTracks($shipment)
    {
        $tracks = [];
        if (!empty($this->_tracks[$shipment->getId()])) {
            $tracks = $this->_tracks[$shipment->getId()];
        }
        return $tracks;
    }

    /**
     * Getter for shipment address by format
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return string
     */
    public function getShipmentAddressFormattedHtml($shipment)
    {
        $shippingAddress = $shipment->getShippingAddress();
        if (!$shippingAddress instanceof \Magento\Sales\Model\Order\Address) {
            return '';
        }
        return $shippingAddress->format('html');
    }

    /**
     * Getter for billing address of order by format
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getBillingAddressFormattedHtml($order)
    {
        $billingAddress = $order->getBillingAddress();
        if (!$billingAddress instanceof \Magento\Sales\Model\Order\Address) {
            return '';
        }
        return $billingAddress->format('html');
    }

    /**
     * Getter for billing address of order by format
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return array
     */
    public function getShipmentItems($shipment)
    {
        $res = [];
        foreach ($shipment->getItemsCollection() as $item) {
            if (!$item->getOrderItem()->getParentItem()) {
                $res[] = $item;
            }
        }
        return $res;
    }
}
