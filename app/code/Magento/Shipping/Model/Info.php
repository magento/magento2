<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model;

use Magento\Framework\DataObject;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\OrderFactory;
use Magento\Shipping\Helper\Data as ShippingHelper;
use Magento\Shipping\Model\Order\Track as OrderTrack;
use Magento\Shipping\Model\Order\TrackFactory as OrderTrackFactory;
use Magento\Shipping\Model\ResourceModel\Order\Track\Collection as OrderTrackCollection;
use Magento\Shipping\Model\ResourceModel\Order\Track\CollectionFactory as OrderTrackCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory as OrderShipmentTrackCollectionFactory;

class Info extends DataObject
{
    /**
     * Tracking info
     *
     * @var array
     */
    protected $_trackingInfo = [];

    /**
     * Shipping data
     *
     * @var ShippingHelper
     */
    protected $_shippingData;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var OrderTrackFactory
     */
    protected $_trackFactory;

    /**
     * @var OrderShipmentTrackCollectionFactory
     */
    protected $_trackCollectionFactory;

    /**
     * @param ShippingHelper $shippingData
     * @param OrderFactory $orderFactory
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param OrderTrackFactory $trackFactory
     * @param OrderTrackCollectionFactory $trackCollectionFactory
     * @param array $data
     */
    public function __construct(
        ShippingHelper $shippingData,
        OrderFactory $orderFactory,
        protected readonly ShipmentRepositoryInterface $shipmentRepository,
        OrderTrackFactory $trackFactory,
        OrderTrackCollectionFactory $trackCollectionFactory,
        array $data = []
    ) {
        $this->_shippingData = $shippingData;
        $this->_orderFactory = $orderFactory;
        $this->_trackFactory = $trackFactory;
        $this->_trackCollectionFactory = $trackCollectionFactory;
        parent::__construct($data);
    }

    /**
     * Generating tracking info
     *
     * @param array $hash
     * @return $this
     */
    public function loadByHash($hash)
    {
        /* @var ShippingHelper $helper */
        $helper = $this->_shippingData;
        $data = $helper->decodeTrackingHash($hash);
        if (!empty($data)) {
            $this->setData($data['key'], $data['id']);
            $this->setProtectCode($data['hash']);

            if ($this->getOrderId() > 0) {
                $this->getTrackingInfoByOrder();
            } elseif ($this->getShipId() > 0) {
                $this->getTrackingInfoByShip();
            } else {
                $this->getTrackingInfoByTrackId();
            }
        }
        return $this;
    }

    /**
     * Retrieve tracking info
     *
     * @return array
     */
    public function getTrackingInfo()
    {
        return $this->_trackingInfo;
    }

    /**
     * Instantiate order model
     *
     * @return Order|bool
     */
    protected function _initOrder()
    {
        /** @var Order $order */
        $order = $this->_orderFactory->create()->load($this->getOrderId());

        if (!$order->getId() || $this->getProtectCode() !== $order->getProtectCode()) {
            return false;
        }

        return $order;
    }

    /**
     * Instantiate ship model
     *
     * @return Shipment|bool
     */
    protected function _initShipment()
    {
        /* @var Shipment $model */
        $ship = $this->shipmentRepository->get($this->getShipId());
        if (!$ship->getEntityId() || $this->getProtectCode() !== $ship->getProtectCode()) {
            return false;
        }

        return $ship;
    }

    /**
     * Retrieve all tracking by order id
     *
     * @return array
     */
    public function getTrackingInfoByOrder()
    {
        $shipTrack = [];
        $order = $this->_initOrder();
        if ($order) {
            $shipments = $order->getShipmentsCollection();
            foreach ($shipments as $shipment) {
                $increment_id = $shipment->getIncrementId();
                $tracks = $this->_getTracksCollection($shipment);

                $trackingInfos = [];
                foreach ($tracks as $track) {
                    $trackingInfos[] = $track->getNumberDetail();
                }
                $shipTrack[$increment_id] = $trackingInfos;
            }
        }
        $this->_trackingInfo = $shipTrack;
        return $this->_trackingInfo;
    }

    /**
     * Retrieve all tracking by ship id
     *
     * @return array
     */
    public function getTrackingInfoByShip()
    {
        $shipTrack = [];
        $shipment = $this->_initShipment();
        if ($shipment) {
            $increment_id = $shipment->getIncrementId();
            $tracks = $this->_getTracksCollection($shipment);

            $trackingInfos = [];
            foreach ($tracks as $track) {
                $trackingInfos[] = $track->getNumberDetail();
            }
            $shipTrack[$increment_id] = $trackingInfos;
        }
        $this->_trackingInfo = $shipTrack;
        return $this->_trackingInfo;
    }

    /**
     * Retrieve tracking by tracking entity id
     *
     * @return array
     */
    public function getTrackingInfoByTrackId()
    {
        /** @var OrderTrack $track */
        $track = $this->_trackFactory->create()->load($this->getTrackId());
        if ($track->getId() && $this->getProtectCode() === $track->getProtectCode()) {
            $this->_trackingInfo = [[$track->getNumberDetail()]];
        }
        return $this->_trackingInfo;
    }

    /**
     * @param Shipment $shipment
     * @return OrderTrackCollection
     */
    protected function _getTracksCollection(Shipment $shipment)
    {
        $tracks = $this->_trackCollectionFactory->create()->setShipmentFilter($shipment->getId());

        if ($shipment->getId()) {
            foreach ($tracks as $track) {
                $track->setShipment($shipment);
            }
        }
        return $tracks;
    }
}
