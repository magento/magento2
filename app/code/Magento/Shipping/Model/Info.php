<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Shipping\Model;

use Magento\Sales\Model\Order\Shipment;

class Info extends \Magento\Framework\DataObject
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
     * @var \Magento\Shipping\Helper\Data
     */
    protected $_shippingData;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var \Magento\Shipping\Model\Order\TrackFactory
     */
    protected $_trackFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory
     */
    protected $_trackCollectionFactory;

    /**
     * @param \Magento\Shipping\Helper\Data $shippingData
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Shipping\Model\Order\TrackFactory $trackFactory
     * @param \Magento\Shipping\Model\ResourceModel\Order\Track\CollectionFactory $trackCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Shipping\Helper\Data $shippingData,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Shipping\Model\Order\TrackFactory $trackFactory,
        \Magento\Shipping\Model\ResourceModel\Order\Track\CollectionFactory $trackCollectionFactory,
        array $data = []
    ) {
        $this->_shippingData = $shippingData;
        $this->_orderFactory = $orderFactory;
        $this->shipmentRepository = $shipmentRepository;
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
        /* @var $helper \Magento\Shipping\Helper\Data */
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
     * @return \Magento\Sales\Model\Order|bool
     */
    protected function _initOrder()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_orderFactory->create()->load($this->getOrderId());

        if (!$order->getId() || $this->getProtectCode() != $order->getProtectCode()) {
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
        /* @var $model Shipment */
        $ship = $this->shipmentRepository->get($this->getShipId());
        if (!$ship->getEntityId() || $this->getProtectCode() != $ship->getProtectCode()) {
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
        /** @var \Magento\Shipping\Model\Order\Track $track */
        $track = $this->_trackFactory->create()->load($this->getTrackId());
        if ($track->getId() && $this->getProtectCode() == $track->getProtectCode()) {
            $this->_trackingInfo = [[$track->getNumberDetail()]];
        }
        return $this->_trackingInfo;
    }

    /**
     * @param Shipment $shipment
     * @return \Magento\Shipping\Model\ResourceModel\Order\Track\Collection
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
