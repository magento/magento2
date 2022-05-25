<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Fixture;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\ShipOrderInterface;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class Shipment implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'order_id' => null,
        'items' => [],
        'notify' => false,
        'append_comment' => false,
        'comment' => null,
        'tracks' => [],
        'packages' => [],
        'arguments' => null,
    ];

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param ServiceFactory $serviceFactory
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        ShipmentRepositoryInterface $shipmentRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Shipment::DEFAULT_DATA.
     * Fields structure fields:
     * - $data['items']: can be supplied in following formats:
     *      - array of arrays [{"sku":"$product1.sku","qty":1}, {"sku":"$product2.sku","qty":1}]
     *      - array of SKUs ["$product1.sku", "$product2.sku"]
     *      - array of order items IDs ["$item1.id", "$item2.id"]
     *      - array of product instances ["$product1", "$product2"]
     */
    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(ShipOrderInterface::class, 'execute');

        $invoiceId = $service->execute($this->prepareData($data));

        return $this->shipmentRepository->get($invoiceId);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $invoice = $this->shipmentRepository->get($data->getId());
        $this->shipmentRepository->delete($invoice);
    }

    /**
     * Prepare shipment data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $data['items'] = $this->prepareShipmentItems($data);

        return $data;
    }

    /**
     * Prepare shipment items
     *
     * @param array $data
     * @return array
     */
    private function prepareShipmentItems(array $data): array
    {
        $shipmentItems = [];
        $order = $this->orderRepository->get($data['order_id']);
        $orderItemIdsBySku = [];
        foreach ($order->getItems() as $item) {
            $orderItemIdsBySku[$item->getSku()] = $item->getItemId();
        }

        foreach ($data['items'] as $itemToShip) {
            $qty = 1;
            $orderItemId = 1;
            $sku = null;
            if (is_numeric($itemToShip)) {
                $orderItemId = $itemToShip;
            } elseif (is_string($itemToShip)) {
                $sku = $itemToShip;
            } elseif ($itemToShip instanceof ProductInterface) {
                $sku = $itemToShip->getSku();
            } else {
                $qty = $itemToShip['qty'] ?? $qty;
                $orderItemId = $itemToShip['order_item_id'] ?? $qty;
                $sku = $itemToShip['sku'] ?? $sku;
            }
            if (!$orderItemId && $sku) {
                $orderItemId = $orderItemIdsBySku[$sku];
            }
            $shipmentItems[] = ['order_item_id' => $orderItemId, 'qty' => $qty];
        }

        return $shipmentItems;
    }
}
