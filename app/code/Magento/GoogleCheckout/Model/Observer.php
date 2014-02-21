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
 * @package     Magento_GoogleCheckout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Google Checkout Event Observer
 *
 * @category   Magento
 * @package    Magento_GoogleCheckout
 */
namespace Magento\GoogleCheckout\Model;

class Observer
{
    /**
     * @var ShippingFactory
     */
    protected $shippingFactory;

    /**
     * @var ApiFactory
     */
    protected $apiFactory;

    /**
     * @param ShippingFactory $shippingFactory
     * @param ApiFactory $apiFactory
     */
    public function __construct(
        \Magento\GoogleCheckout\Model\ShippingFactory $shippingFactory,
        \Magento\GoogleCheckout\Model\ApiFactory $apiFactory
    ) {
        $this->shippingFactory = $shippingFactory;
        $this->apiFactory = $apiFactory;
    }

    public function salesOrderShipmentTrackSaveAfter(\Magento\Event\Observer $observer)
    {
        $track = $observer->getEvent()->getTrack();

        $order = $track->getShipment()->getOrder();
        $shippingMethod = $order->getShippingMethod(); // String in format of 'carrier_method'
        if (!$shippingMethod) {
            return;
        }

        // Process only Google Checkout internal methods
        /* @var $gcCarrier \Magento\GoogleCheckout\Model\Shipping */
        $gcCarrier = $this->shippingFactory->create();
        list($carrierCode, $methodCode) = explode('_', $shippingMethod);
        if ($gcCarrier->getCarrierCode() != $carrierCode) {
            return;
        }
        $internalMethods = $gcCarrier->getInternallyAllowedMethods();
        if (!isset($internalMethods[$methodCode])) {
            return;
        }

        $this->apiFactory->create()
            ->setStoreId($order->getStoreId())
            ->deliver($order->getExtOrderId(), $track->getCarrierCode(), $track->getNumber());
    }

    /*
     * Performs specifical actions on Google Checkout internal shipments saving
     *
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function salesOrderShipmentSaveAfter(\Magento\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $shippingMethod = $order->getShippingMethod(); // String in format of 'carrier_method'
        if (!$shippingMethod) {
            return;
        }

        // Process only Google Checkout internal methods
        /* @var $gcCarrier \Magento\GoogleCheckout\Model\Shipping */
        $gcCarrier = $this->shippingFactory->create();
        list($carrierCode, $methodCode) = explode('_', $shippingMethod);
        if ($gcCarrier->getCarrierCode() != $carrierCode) {
            return;
        }
        $internalMethods = $gcCarrier->getInternallyAllowedMethods();
        if (!isset($internalMethods[$methodCode])) {
            return;
        }

        // Process this saving
        $items = array();
        foreach ($shipment->getAllItems() as $item) {
            if ($item->getOrderItem()->getParentItemId()) {
                continue;
            }
            $items[] = $item->getSku();
        }

        if ($items) {
            $this->apiFactory->create()
                ->setStoreId($order->getStoreId())
                ->shipItems($order->getExtOrderId(), $items);
        }
    }
}
