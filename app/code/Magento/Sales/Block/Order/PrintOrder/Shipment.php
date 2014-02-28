<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales order details block
 */
namespace Magento\Sales\Block\Order\PrintOrder;

class Shipment extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Tracks for Shippings
     *
     * @var array
     */
    protected $_tracks = array();

     /**
     * Order shipments collection
     *
     * @var array|\Magento\Sales\Model\Resource\Order\Shipment\Collection
     */
    protected $_shipmentsCollection;

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Payment\Helper\Data $paymentHelper,
        array $data = array()
    ) {
        $this->_paymentHelper = $paymentHelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Load all tracks and save it to local cache by shipments
     *
     * @return \Magento\Sales\Block\Order\PrintOrder\Shipment
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
            $this->_shipmentsCollection = array($shipment);
        } else {
            $this->_shipmentsCollection = $this->getOrder()->getShipmentsCollection();
        }

        return parent::_beforeToHtml();
    }

    protected function _prepareLayout()
    {
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle(__('Order # %1', $this->getOrder()->getRealOrderId()));
        }
        $this->setChild(
            'payment_info',
            $this->_paymentHelper->getInfoBlock($this->getOrder()->getPayment())
        );
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

    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }

    protected function _prepareItem(\Magento\View\Element\AbstractBlock $renderer)
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
        $tracks = array();
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
        if(!($shippingAddress instanceof \Magento\Sales\Model\Order\Address)) {
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
        if (!($billingAddress instanceof \Magento\Sales\Model\Order\Address)) {
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
        $res = array();
        foreach ($shipment->getItemsCollection() as $item) {
            if (!$item->getOrderItem()->getParentItem()) {
                $res[] = $item;
            }
        }
        return $res;
    }
}

