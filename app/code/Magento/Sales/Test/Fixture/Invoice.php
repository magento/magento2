<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Fixture;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DataObject;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

class Invoice implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'order_id' => null,
        'capture' => false,
        'items' => [],
        'notify' => false,
        'append_comment' => false,
        'comment' => null,
        'arguments' => null,
    ];

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param ServiceFactory $serviceFactory
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as Invoice::DEFAULT_DATA.
     * Fields structure fields:
     * - $data['items']: can be supplied in following formats:
     *      - array of arrays [{"sku":"$product1.sku","qty":1}, {"sku":"$product2.sku","qty":1}]
     *      - array of SKUs ["$product1.sku", "$product2.sku"]
     *      - array of order items IDs ["$item1.id", "$item2.id"]
     *      - array of product instances ["$product1", "$product2"]
     */
    public function apply(array $data = []): ?DataObject
    {
        $service = $this->serviceFactory->create(InvoiceOrderInterface::class, 'execute');

        $invoiceId = $service->execute($this->prepareData($data));

        return $this->invoiceRepository->get($invoiceId);
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $invoice = $this->invoiceRepository->get($data->getId());
        $this->invoiceRepository->delete($invoice);
    }

    /**
     * Prepare invoice data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $data['items'] = $this->prepareInvoiceItems($data);

        return $data;
    }

    /**
     * Prepare invoice items
     *
     * @param array $data
     * @return array
     */
    private function prepareInvoiceItems(array $data): array
    {
        $items = [];
        $order = $this->orderRepository->get($data['order_id']);
        $orderItemIdsBySku = [];
        foreach ($order->getItems() as $item) {
            $orderItemIdsBySku[$item->getSku()] = $item->getItemId();
        }

        foreach ($data['items'] as $itemToInvoice) {
            $qty = 1;
            $orderItemId = 1;
            $sku = null;
            if (is_numeric($itemToInvoice)) {
                $orderItemId = $itemToInvoice;
            } elseif (is_string($itemToInvoice)) {
                $sku = $itemToInvoice;
            } elseif ($itemToInvoice instanceof ProductInterface) {
                $sku = $itemToInvoice->getSku();
            } else {
                $qty = $itemToInvoice['qty'] ?? $qty;
                $orderItemId = $itemToInvoice['order_item_id'] ?? $qty;
                $sku = $itemToInvoice['sku'] ?? $sku;
            }
            if (!$orderItemId && $sku) {
                $orderItemId = $orderItemIdsBySku[$sku];
            }
            $items[] = ['order_item_id' => $orderItemId, 'qty' => $qty];
        }

        return $items;
    }
}
