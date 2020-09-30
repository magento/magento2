<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin;

use Exception;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment\Item;
use Magento\Sales\Model\Order\ShipmentRepository;
use Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader;

/**
 * Plugin to update order data before and after saving shipment via API
 */
class ProcessOrderAndShipmentViaAPI
{
    /**
     * @var ShipmentLoader
     */
    private $shipmentLoader;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * Init plugin
     *
     * @param ShipmentLoader $shipmentLoader
     * @param Transaction $transaction
     */
    public function __construct(
        ShipmentLoader $shipmentLoader,
        Transaction $transaction
    ) {
        $this->shipmentLoader = $shipmentLoader;
        $this->transaction = $transaction;
    }

    /**
     * Process shipping details before saving shipment via API
     *
     * @param ShipmentRepository $shipmentRepository
     * @param ShipmentInterface $shipmentData
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeSave(
        ShipmentRepository $shipmentRepository,
        ShipmentInterface $shipmentData
    ): array {
        $this->shipmentLoader->setOrderId($shipmentData->getOrderId());
        $trackData = !empty($shipmentData->getTracks()) ?
            $this->getShipmentTracking($shipmentData) : [];
        $this->shipmentLoader->setTracking($trackData);
        $shipmentItems = !empty($shipmentData) ?
            $this->getShipmentItems($shipmentData) : [];
        $orderItems = [];
        if (!empty($shipmentData)) {
            $order = $shipmentData->getOrder();
            $orderItems = $order ? $this->getOrderItems($order) : [];
        }
        $data = (!empty($shipmentItems) && !empty($orderItems)) ?
            $this->getShippingData($shipmentItems, $orderItems) : [];
        $this->shipmentLoader->setShipment($data);
        $shipment = $this->shipmentLoader->load();
        $shipment = empty($shipment) ? $shipmentData
            : $this->processShippingDetails($shipmentData, $shipment);
        return [$shipment];
    }

    /**
     * Save order data after saving shipment via API
     *
     * @param ShipmentRepository $shipmentRepository
     * @param ShipmentInterface $shipment
     * @return ShipmentInterface
     * @throws Exception
     */
    public function afterSave(
        ShipmentRepository $shipmentRepository,
        ShipmentInterface $shipment
    ): ShipmentInterface {
        $shipmentDetails = $shipmentRepository->get($shipment->getEntityId());
        $order = $shipmentDetails->getOrder();
        $shipmentItems = !empty($shipment) ?
            $this->getShipmentItems($shipment) : [];
        $this->processOrderItems($order, $shipmentItems);
        $order->setIsInProcess(true);
        $this->transaction
            ->addObject($order)
            ->save();
        return $shipment;
    }

    /**
     * Process shipment items
     *
     * @param ShipmentInterface $shipment
     * @return array
     * @throws LocalizedException
     */
    private function getShipmentItems(ShipmentInterface $shipment): array
    {
        $shipmentItems = [];
        foreach ($shipment->getItems() as $item) {
            $sku = $item->getSku();
            if (isset($sku)) {
                $shipmentItems[$sku]['qty'] = $item->getQty();
            }
        }
        return $shipmentItems;
    }

    /**
     * Get shipment tracking data from the shipment array
     *
     * @param ShipmentInterface $shipment
     * @return array
     */
    private function getShipmentTracking(ShipmentInterface $shipment): array
    {
        $trackData = [];
        foreach ($shipment->getTracks() as $key => $track) {
            $trackData[$key]['number'] = $track->getTrackNumber();
            $trackData[$key]['title'] = $track->getTitle();
            $trackData[$key]['carrier_code'] = $track->getCarrierCode();
        }
        return $trackData;
    }

    /**
     * Get orderItems from shipment order
     *
     * @param Order $order
     * @return array
     */
    private function getOrderItems(Order $order): array
    {
        $orderItems = [];
        foreach ($order->getItems() as $item) {
            $orderItems[$item->getSku()] = $item->getItemId();
        }
        return $orderItems;
    }

    /**
     * Get available shipping data from shippingItems and orderItems
     *
     * @param array $shipmentItems
     * @param array $orderItems
     * @return array
     * @throws LocalizedException
     */
    private function getShippingData(array $shipmentItems, array $orderItems): array
    {
        $data = [];
        foreach ($shipmentItems as $shippingItemSku => $shipmentItem) {
            if (isset($orderItems[$shippingItemSku])) {
                $itemId = (int) $orderItems[$shippingItemSku];
                $data['items'][$itemId] = $shipmentItem['qty'];
            }
        }
        return $data;
    }

    /**
     * Process shipping comments if available
     *
     * @param ShipmentInterface $shipmentData
     * @param ShipmentInterface $shipment
     * @return void
     */
    private function processShippingComments(ShipmentInterface $shipmentData, ShipmentInterface $shipment): void
    {
        foreach ($shipmentData->getComments() as $comment) {
            $shipment->addComment(
                $comment->getComment(),
                $comment->getIsCustomerNotified(),
                $comment->getIsVisibleOnFront()
            );
            $shipment->setCustomerNote($comment->getComment());
            $shipment->setCustomerNoteNotify((bool) $comment->getIsCustomerNotified());
        }
    }

    /**
     * Process shipping details
     *
     * @param ShipmentInterface $shipmentData
     * @param ShipmentInterface $shipment
     * @return ShipmentInterface
     */
    private function processShippingDetails(
        ShipmentInterface $shipmentData,
        ShipmentInterface $shipment
    ): ShipmentInterface {
        if (empty($shipment->getItems())) {
            $shipment->setItems($shipmentData->getItems());
        }
        if (!empty($shipmentData->getComments())) {
            $this->processShippingComments($shipmentData, $shipment);
        }
        if ((int) $shipment->getTotalQty() < 1) {
            $shipment->setTotalQty($shipmentData->getTotalQty());
        }
        return $shipment;
    }

    /**
     * Process order items data and set the proper item qty
     *
     * @param Order $order
     * @param array $shipmentItems
     * @throws LocalizedException
     */
    private function processOrderItems(Order $order, array $shipmentItems): void
    {
        /** @var Item $item */
        foreach ($order->getAllItems() as $item) {
            if (isset($shipmentItems[$item->getSku()])) {
                $qty = (float)$shipmentItems[$item->getSku()]['qty'];
                $item->setQty($qty);
                if ((float)$item->getQtyToShip() > 0) {
                    $item->setQtyShipped((float)$item->getQtyToShip());
                }
            }
        }
    }
}
