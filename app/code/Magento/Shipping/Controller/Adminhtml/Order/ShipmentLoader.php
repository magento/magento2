<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory;
use Magento\Sales\Api\Data\ShipmentItemCreationInterfaceFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;

/**
 * Loader for shipment
 *
 * @method ShipmentLoader setOrderId($id)
 * @method ShipmentLoader setShipmentId($id)
 * @method ShipmentLoader setShipment($shipment)
 * @method ShipmentLoader setTracking($tracking)
 * @method int getOrderId()
 * @method int getShipmentId()
 * @method array getTracking()
 */
class ShipmentLoader extends DataObject
{
    const SHIPMENT = 'shipment';

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ShipmentDocumentFactory
     */
    private $documentFactory;

    /**
     * @var ShipmentTrackCreationInterfaceFactory
     */
    private $trackFactory;

    /**
     * @var ShipmentItemCreationInterfaceFactory
     */
    private $itemFactory;

    /**
     * @param ManagerInterface $messageManager
     * @param Registry $registry
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param ShipmentDocumentFactory $documentFactory
     * @param ShipmentTrackCreationInterfaceFactory $trackFactory
     * @param ShipmentItemCreationInterfaceFactory $itemFactory
     * @param array $data
     */
    public function __construct(
        ManagerInterface $messageManager,
        Registry $registry,
        ShipmentRepositoryInterface $shipmentRepository,
        OrderRepositoryInterface $orderRepository,
        ShipmentDocumentFactory $documentFactory,
        ShipmentTrackCreationInterfaceFactory $trackFactory,
        ShipmentItemCreationInterfaceFactory $itemFactory,
        array $data = []
    ) {
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->shipmentRepository = $shipmentRepository;
        $this->orderRepository = $orderRepository;
        $this->documentFactory = $documentFactory;
        $this->trackFactory = $trackFactory;
        $this->itemFactory = $itemFactory;
        parent::__construct($data);
    }

    /**
     * Initialize shipment model instance
     *
     * @return bool|\Magento\Sales\Model\Order\Shipment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function load()
    {
        $shipment = false;
        $orderId = $this->getOrderId();
        $shipmentId = $this->getShipmentId();
        if ($shipmentId) {
            try {
                $shipment = $this->shipmentRepository->get($shipmentId);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('This shipment no longer exists.'));
                return false;
            }
        } elseif ($orderId) {
            $order = $this->orderRepository->get($orderId);

            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->messageManager->addError(__('The order no longer exists.'));
                return false;
            }
            /**
             * Check shipment is available to create separate from invoice
             */
            if ($order->getForcedShipmentWithInvoice()) {
                $this->messageManager->addError(__('Cannot do shipment for the order separately from invoice.'));
                return false;
            }
            /**
             * Check shipment create availability
             */
            if (!$order->canShip()) {
                $this->messageManager->addError(__('Cannot do shipment for the order.'));
                return false;
            }

            $shipmentItems = $this->getShipmentItems($this->getShipment());

            $shipment = $this->documentFactory->create(
                $order,
                $shipmentItems,
                $this->getTrackingArray()
            );
        }

        $this->registry->register('current_shipment', $shipment);
        return $shipment;
    }

    /**
     * Convert UI-generated tracking array to Data Object array
     *
     * @return ShipmentTrackCreationInterface[]
     * @throws LocalizedException
     */
    private function getTrackingArray()
    {
        $tracks = $this->getTracking() ?: [];
        $trackingCreation = [];
        foreach ($tracks as $track) {
            if (!isset($track['number']) || !isset($track['title']) || !isset($track['carrier_code'])) {
                throw new LocalizedException(
                    __('Tracking information must contain title, carrier code, and tracking number')
                );
            }
            /** @var ShipmentTrackCreationInterface $trackCreation */
            $trackCreation = $this->trackFactory->create();
            $trackCreation->setTrackNumber($track['number']);
            $trackCreation->setTitle($track['title']);
            $trackCreation->setCarrierCode($track['carrier_code']);
            $trackingCreation[] = $trackCreation;
        }

        return $trackingCreation;
    }

    /**
     * Extract product id => product quantity array from shipment data.
     *
     * @param array $shipmentData
     * @return int[]
     */
    private function getShipmentItems(array $shipmentData)
    {
        $shipmentItems = [];
        $itemQty = isset($shipmentData['items']) ? $shipmentData['items'] : [];
        foreach ($itemQty as $itemId => $quantity) {
            /** @var ShipmentItemCreationInterface $item */
            $item = $this->itemFactory->create();
            $item->setOrderItemId($itemId);
            $item->setQty($quantity);
            $shipmentItems[] = $item;
        }
        return $shipmentItems;
    }

    /**
     * Retrieve shipment
     *
     * @return array
     */
    public function getShipment()
    {
        return $this->getData(self::SHIPMENT) ?: [];
    }
}
