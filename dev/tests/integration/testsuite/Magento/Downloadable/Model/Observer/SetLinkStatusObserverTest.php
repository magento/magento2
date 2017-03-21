<?php

namespace Magento\Downloadable\Model\Observer;

/**
 * Integration test for case, when customer is able to download
 * downloadable product, after order was canceled.
 */
class SetLinkStatusObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Order collection
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * Initialization of dependencies
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->orderItemFactory = $this->objectManager->create(
            \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory::class
        );
        $this->orderRepository = $this->objectManager->get(
            \Magento\Sales\Model\OrderRepository::class
        );
    }

    /**
     * Asserting, that links status is expired after canceling of order.
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable_with_files.php
     */
    public function testCheckStatusOnOrderCancel()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->prepareOrder();
        $order = $this->orderRepository->save($order);
        if ($orderItems = $order->getAllItems()) {

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

    /**
     * Prepare specific order with downloadable items
     * and configured links
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @return \Magento\Sales\Model\Order
     */
    protected function prepareOrder()
    {
        $billingAddress = $this->objectManager->create(
            \Magento\Sales\Model\Order\Address::class,
            [
                'data' => [
                    'firstname' => 'guest',
                    'lastname' => 'guest',
                    'email' => 'customer@example.com',
                    'street' => 'street',
                    'city' => 'Los Angeles',
                    'region' => 'CA',
                    'postcode' => '1',
                    'country_id' => 'US',
                    'telephone' => '1',
                ]
            ]
        );
        $billingAddress->setAddressType('billing');

        $payment = $this->objectManager->create(
            \Magento\Sales\Model\Order\Payment::class);
        $payment->setMethod('checkmo');

        $orderItem = $this->objectManager->create(
            \Magento\Sales\Model\Order\Item::class);
        $orderItem->setProductId(
            1
        )->setQtyOrdered(
            1
        )->setProductType(
            \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
        )->setProductOptions([
                'links' => [1]
            ]
        );

        $order = $this->objectManager->create(
            \Magento\Sales\Model\Order::class
        );

        $order->setCustomerEmail('mail@to.co')
            ->addItem(
                $orderItem
            )->setIncrementId(
                '100000001'
            )->setCustomerIsGuest(
                true
            )->setStoreId(
                1
            )->setEmailSent(
                0
            )->setBillingAddress(
                $billingAddress
            )->setPayment(
                $payment
            );
        return $order;
    }
}