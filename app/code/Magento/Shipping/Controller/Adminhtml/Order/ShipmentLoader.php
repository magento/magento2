<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order;

use Magento\Framework\DataObject;

/**
 * Class ShipmentLoader
 *
 * @package Magento\Shipping\Controller\Adminhtml\Order
 * @method ShipmentLoader setOrderId($id)
 * @method ShipmentLoader setShipmentId($id)
 * @method ShipmentLoader setShipment($shipment)
 * @method ShipmentLoader setTracking($tracking)
 * @method int getOrderId()
 * @method int getShipmentId()
 * @method array getShipment()
 * @method array getTracking()
 * @since 2.0.0
 */
class ShipmentLoader extends DataObject
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.0.0
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registry;

    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     * @since 2.0.0
     */
    protected $shipmentRepository;

    /**
     * @var \Magento\Sales\Model\Order\ShipmentFactory
     * @since 2.0.0
     */
    protected $shipmentFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     * @since 2.0.0
     */
    protected $trackFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     * @since 2.0.0
     */
    protected $orderRepository;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository
     * @param \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentFactory = $shipmentFactory;
        $this->trackFactory = $trackFactory;
        $this->orderRepository = $orderRepository;
        parent::__construct($data);
    }

    /**
     * Initialize shipment items QTY
     *
     * @return array
     * @since 2.0.0
     */
    protected function getItemQtys()
    {
        $data = $this->getShipment();

        return isset($data['items']) ? $data['items'] : [];
    }

    /**
     * Initialize shipment model instance
     *
     * @return bool|\Magento\Sales\Model\Order\Shipment
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function load()
    {
        $shipment = false;
        $orderId = $this->getOrderId();
        $shipmentId = $this->getShipmentId();
        if ($shipmentId) {
            $shipment = $this->shipmentRepository->get($shipmentId);
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

            $shipment = $this->shipmentFactory->create(
                $order,
                $this->getItemQtys(),
                $this->getTracking()
            );
        }

        $this->registry->register('current_shipment', $shipment);
        return $shipment;
    }
}
