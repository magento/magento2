<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
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

/**
 * Invoice fixture
 *
 * Usage examples:
 *
 * 1. Create an invoice for an order using default data:
 * <pre>
 *  #[
 *      DataFixture(InvoiceFixture::class, ['order_id' => '$order.id$'], 'invoice')
 *  ]
 * </pre>
 *
 * 2. Create an invoice and capture payment:
 * <pre>
 *  #[
 *      DataFixture(
 *          InvoiceFixture::class,
 *          ['order_id' => '$order.id$', 'capture' => true],
 *          'invoice'
 *      )
 *  ]
 * </pre>
 *
 * 3. Create an invoice for specific items (by SKU) with quantities:
 * <pre>
 *  #[
 *      DataFixture(
 *          InvoiceFixture::class,
 *          [
 *              'order_id' => '$order.id$',
 *              'items' => [
 *                  ['sku' => '$p1.sku$', 'qty' => 1],
 *                  ['sku' => '$p2.sku$', 'qty' => 2],
 *              ]
 *          ],
 *          'invoice'
 *      )
 *  ]
 * </pre>
 */
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
     *
     * Fields structure:
     *
     * - `$data['items']` as array of arrays with sku and qty:
     * <pre>
     * ['items' => [['sku' => '$p1.sku$', 'qty' => 1], ['sku' => '$p2.sku$', 'qty' => 1]]]
     * </pre>
     * - `$data['items']` as array of arrays with order_item_id and qty:
     * <pre>
     * ['items' => [['order_item_id' => '$oItem1.id$', 'qty' => 1], ['order_item_id' => '$oItem2.id$', 'qty' => 1]]]
     * </pre>
     * - `$data['items']` as array of arrays with product_id and qty:
     * <pre>
     * ['items' => [['product_id' => '$p1.id$', 'qty' => 1], ['product_id' => '$p2.id$', 'qty' => 1]]]
     * </pre>
     * - `$data['items']` as array of arrays with quote_item_id and qty:
     * <pre>
     * ['items' => [['quote_item_id' => '$qItem1.id$', 'qty' => 1], ['quote_item_id' => '$qItem2.id$', 'qty' => 1]]]
     * </pre>
     * - `$data['items']` as array of SKUs:
     * <pre>
     * ['items' => ['$p1.sku$', '$p2.sku$']]
     * </pre>
     * - `$data['items']` as array of order item IDs:
     * <pre>
     * ['items' => ['$oItem1.id$', '$oItem2.id$']]
     * </pre>
     * - `$data['items']` as array of product instances:
     * <pre>
     * ['items' => ['$p1$', '$p2$']]
     * </pre>
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
        $invoiceItems = [];
        $order = $this->orderRepository->get($data['order_id']);
        $orderItemIdsBySku = [];
        $orderItemIdsByProductIds = [];
        $orderItemIdsByQuoteItemIds = [];
        foreach ($order->getItems() as $item) {
            $orderItemIdsBySku[$item->getSku()] = $item->getItemId();
            $orderItemIdsByQuoteItemIds[$item->getQuoteItemId()] = $item->getItemId();
            $orderItemIdsByProductIds[$item->getProductId()] = $item->getItemId();
        }

        foreach ($data['items'] as $itemToInvoice) {
            $invoiceItem = ['order_item_id' => null, 'qty' => 1];
            if (is_numeric($itemToInvoice)) {
                $invoiceItem['order_item_id'] = $itemToInvoice;
            } elseif (is_string($itemToInvoice)) {
                $invoiceItem['order_item_id'] = $orderItemIdsBySku[$itemToInvoice];
            } elseif ($itemToInvoice instanceof ProductInterface) {
                $invoiceItem['order_item_id'] = $orderItemIdsBySku[$itemToInvoice->getSku()];
            } else {
                $invoiceItem = array_intersect_key($itemToInvoice, $invoiceItem) + $invoiceItem;
                if (isset($itemToInvoice['sku'])) {
                    $invoiceItem['order_item_id'] = $orderItemIdsBySku[$itemToInvoice['sku']];
                } elseif (isset($itemToInvoice['product_id'])) {
                    $invoiceItem['order_item_id'] = $orderItemIdsByProductIds[$itemToInvoice['product_id']];
                } elseif (isset($itemToInvoice['quote_item_id'])) {
                    $invoiceItem['order_item_id'] = $orderItemIdsByQuoteItemIds[$itemToInvoice['quote_item_id']];
                }
            }
            $invoiceItems[] = $invoiceItem;
        }

        return $invoiceItems;
    }
}
