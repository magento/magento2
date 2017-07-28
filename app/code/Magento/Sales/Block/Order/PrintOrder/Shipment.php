<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order\PrintOrder;

use Magento\Framework\View\Element\AbstractBlock;

/**
 * Sales order details block
 *
 * @api
 * @since 2.0.0
 */
class Shipment extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Tracks for Shippings
     *
     * @var array
     * @since 2.0.0
     */
    protected $tracks = [];

    /**
     * Order shipments collection
     *
     * @var array|\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection
     * @since 2.0.0
     */
    protected $shipmentsCollection;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Payment\Helper\Data
     * @since 2.0.0
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     * @since 2.0.0
     */
    protected $addressRenderer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        array $data = []
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->coreRegistry = $registry;
        $this->addressRenderer = $addressRenderer;
        parent::__construct($context, $data);
    }

    /**
     * Load all tracks and save it to local cache by shipments
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $tracksCollection = $this->getOrder()->getTracksCollection();

        foreach ($tracksCollection->getItems() as $track) {
            $shipmentId = $track->getParentId();
            $this->tracks[$shipmentId][] = $track;
        }

        $shipment = $this->coreRegistry->registry('current_shipment');
        if ($shipment) {
            $this->shipmentsCollection = [$shipment];
        } else {
            $this->shipmentsCollection = $this->getOrder()->getShipmentsCollection();
        }

        return parent::_beforeToHtml();
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Order # %1', $this->getOrder()->getRealOrderId()));
        $infoBlock = $this->paymentHelper->getInfoBlock($this->getOrder()->getPayment(), $this->getLayout());
        $this->setChild('payment_info', $infoBlock);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/history');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getPrintUrl()
    {
        return $this->getUrl('*/*/print');
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    /**
     * @return array|null
     * @since 2.0.0
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * @return array|null
     * @since 2.0.0
     */
    public function getShipment()
    {
        return $this->coreRegistry->registry('current_shipment');
    }

    /**
     * @param AbstractBlock $renderer
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareItem(AbstractBlock $renderer)
    {
        $renderer->setPrintStatus(true);

        return parent::_prepareItem($renderer);
    }

    /**
     * Retrieve order shipments collection
     *
     * @return array|\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection
     * @since 2.0.0
     */
    public function getShipmentsCollection()
    {
        return $this->shipmentsCollection;
    }

    /**
     * Getter for order tracking numbers collection per shipment
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return array
     * @since 2.0.0
     */
    public function getShipmentTracks($shipment)
    {
        $tracks = [];
        if (!empty($this->tracks[$shipment->getId()])) {
            $tracks = $this->tracks[$shipment->getId()];
        }
        return $tracks;
    }

    /**
     * Getter for shipment address by format
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return string
     * @since 2.0.0
     */
    public function getShipmentAddressFormattedHtml($shipment)
    {
        $shippingAddress = $shipment->getShippingAddress();
        if (!$shippingAddress instanceof \Magento\Sales\Model\Order\Address) {
            return '';
        }
        return $this->addressRenderer->format($shippingAddress, 'html');
    }

    /**
     * Getter for billing address of order by format
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     * @since 2.0.0
     */
    public function getBillingAddressFormattedHtml($order)
    {
        $billingAddress = $order->getBillingAddress();
        if (!$billingAddress instanceof \Magento\Sales\Model\Order\Address) {
            return '';
        }
        return $this->addressRenderer->format($billingAddress, 'html');
    }

    /**
     * Getter for billing address of order by format
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return array
     * @since 2.0.0
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
