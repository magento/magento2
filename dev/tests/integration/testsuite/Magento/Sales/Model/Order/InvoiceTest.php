<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Framework\DB\Transaction;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Item\Collection as InvoiceItemCollection;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Invoice model test.
 */
class InvoiceTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var OrderCollection
     */
    private $collection;

    /**
     * @var InvoiceManagementInterface
     */
    private $invoiceManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->collection = $this->objectManager->create(OrderCollection::class);
        $this->invoiceManagement = $this->objectManager->get(InvoiceManagementInterface::class);
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     */
    public function testOrderTotalItemCount()
    {
        $expectedResult = [['total_item_count' => 1]];
        $actualResult = [];
        /** @var \Magento\Sales\Model\Order $order */
        foreach ($this->collection->getItems() as $order) {
            $actualResult[] = ['total_item_count' => $order->getData('total_item_count')];
        }
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Test order with exactly one configurable.
     *
     * @return void
     * @magentoDataFixture Magento/Sales/_files/order_configurable_product.php
     */
    public function testLastInvoiceWithConfigurable(): void
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', '100000001')
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria);
        $orders = $orders->getItems();
        $order = array_shift($orders);
        $invoice = $this->invoiceManagement->prepareInvoice($order);

        self::assertEquals($invoice->isLast(), true);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testPreparedInvoiceItemsArePersistedAsArray(): void
    {
        $order = $this->objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
        /** @var Invoice $invoice */
        $invoice = $this->invoiceManagement->prepareInvoice($order);
        $items = $invoice->getItems();
        self::assertIsArray($items);
        self::assertNotEmpty($items);
        self::assertInstanceOf(InvoiceItemCollection::class, $invoice->getItemsCollection());
        self::assertSame($items, $invoice->getItems());

        $invoice->register();
        $invoice->getOrder()->setIsInProcess(true);
        $this->objectManager->create(Transaction::class)
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

        self::assertIsArray($invoice->getItems());
        $persistedItems = $this->objectManager->create(InvoiceItemCollection::class)
            ->setInvoiceFilter($invoice->getId());
        self::assertCount(count($items), $persistedItems);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     */
    public function testItemsStayArrayAfterItemsCollectionIsRequested(): void
    {
        $invoice = $this->getFixtureInvoice();

        $collection = $invoice->getItemsCollection();
        self::assertInstanceOf(InvoiceItemCollection::class, $collection);
        self::assertNotCount(0, $collection);
        $items = $invoice->getItems();
        self::assertIsArray($items);
        self::assertCount(count($collection), $items);
        self::assertCount(count($items), $invoice->getAllItems());

        $invoiceData = $this->objectManager->get(DataObjectProcessor::class)
            ->buildOutputDataArray($invoice, InvoiceInterface::class);
        self::assertIsArray($invoiceData[InvoiceInterface::ITEMS]);
        self::assertCount(count($items), $invoiceData[InvoiceInterface::ITEMS]);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     */
    public function testItemsCollectionIsCollectionAfterItemsAreRequested(): void
    {
        $invoice = $this->getFixtureInvoice();

        $items = $invoice->getItems();
        self::assertIsArray($items);
        $collection = $invoice->getItemsCollection();
        self::assertInstanceOf(InvoiceItemCollection::class, $collection);
        self::assertSame(array_values($items), array_values($collection->getItems()));
    }

    /**
     * Load the fixture invoice without any items state.
     *
     * @return Invoice
     */
    private function getFixtureInvoice(): Invoice
    {
        $order = $this->objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');
        $invoiceId = $order->getInvoiceCollection()->getFirstItem()->getId();
        self::assertNotEmpty($invoiceId);

        return $this->objectManager->create(Invoice::class)->load($invoiceId);
    }
}
