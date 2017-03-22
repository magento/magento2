<?php

namespace Magento\Downloadable\Model\Observer;

/**
 * Integration test for case, when customer is able to download
 * downloadable product, after order was canceled.
 */
class SetLinkStatusObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Order repository
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * Initialization of dependencies
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->orderRepository = $this->objectManager->get(
            \Magento\Sales\Api\OrderRepositoryInterface::class
        );
    }

    /**
     * Asserting, that links status is expired after canceling of order.
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product_with_links.php
     */
    public function testCheckStatusOnOrderCancel()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get(1);

        $orderItems = $order->getAllItems();
        $items = array_values($orderItems);
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = array_shift($items);

        /** @var \Magento\Sales\Model\Service\InvoiceService $invoiceService */
        $invoiceService = $this->objectManager->create(
            \Magento\Sales\Model\Service\InvoiceService::class
        );

        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $invoiceService->prepareInvoice($order);

        /** Register invoice */
        $invoice->register();
        $invoice->save();

        /** @var \Magento\Framework\DB\Transaction $transactionService */
        $transactionService = $this->objectManager->create(
            \Magento\Framework\DB\Transaction::class
        );
        $transactionService->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

        /** Canceling order to reproduce test case */
        $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
        $order->save();

        /** @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection $linkCollection */
        $linkCollection = $this->objectManager->create(
            \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory::class
        )->create();

        $linkCollection->addFieldToFilter('order_item_id', $orderItem->getId());

        /** @var \Magento\Downloadable\Model\Link\Purchased\Item $linkItem */
        foreach ($linkCollection->getItems() as $linkItem) {
            $this->assertEquals(
                \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED,
                $linkItem->getStatus()
            );
        }
    }
}
