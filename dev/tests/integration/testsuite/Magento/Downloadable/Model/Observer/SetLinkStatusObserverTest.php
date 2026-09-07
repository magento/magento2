<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Downloadable\Model\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Downloadable\Model\Link\Purchased\Item;
use Magento\Downloadable\Model\Product\Type;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Integration test for case, when customer is able to download
 * downloadable product, after order was canceled.
 */
class SetLinkStatusObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Initialization of dependencies
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    /**
     * Asserting, that links status is expired after canceling of order.
     * This test relates to the GitHub issue magento/magento2#8515.
     *
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDataFixture Magento/Downloadable/_files/order_with_downloadable_product.php
     * @magentoDbIsolation disabled
     */
    public function testCheckStatusOnOrderCancel()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId('100000001');

        $orderItems = $order->getAllItems();
        $items = array_values($orderItems);
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = array_shift($items);

        /** Canceling order to reproduce test case */
        $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED);
        $order->save();

        /** @var \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection $linkCollection */
        $linkCollection = $this->objectManager->create(
            \Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\CollectionFactory::class
        )->create();

        $linkCollection->addFieldToFilter('order_item_id', $orderItem->getId());

        /** Assert there are items in linkCollection to avoid false-positive test result. */
        $this->assertGreaterThan(0, $linkCollection->count());

        /** @var \Magento\Downloadable\Model\Link\Purchased\Item $linkItem */
        foreach ($linkCollection->getItems() as $linkItem) {
            $this->assertEquals(
                \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED,
                $linkItem->getStatus()
            );
        }
    }

    /**
     * Order items have no id yet when sales_order_save_after fires on a first order save,
     * so a branch that keys $downloadableItemsStatuses by $item->getId() writes an unusable key.
     * Holded's intended status equals the row's creation default, so only Pending Payment and
     * Payment Review are distinguishing.
     *
     * @magentoDataFixture Magento/Downloadable/_files/product_downloadable.php
     * @magentoDbIsolation enabled
     */
    #[DataProvider('firstSaveOrderStateDataProvider')]
    public function testLinkStatusOnFirstSaveOfOrderInNonProcessingState(string $state, string $expectedLinkStatus)
    {
        $order = $this->saveNewOrderWithDownloadableItemInState($state);
        $orderItem = current($order->getAllItems());

        /** @var CollectionFactory $collectionFactory */
        $collectionFactory = $this->objectManager->get(CollectionFactory::class);
        $linkCollection = $collectionFactory->create();
        $linkCollection->addFieldToFilter('order_item_id', $orderItem->getId());

        $this->assertGreaterThan(0, $linkCollection->count());

        /** @var Item $linkItem */
        foreach ($linkCollection->getItems() as $linkItem) {
            $this->assertEquals($expectedLinkStatus, $linkItem->getStatus());
        }
    }

    /**
     * @return array
     */
    public static function firstSaveOrderStateDataProvider(): array
    {
        return [
            'pending payment' => [Order::STATE_PENDING_PAYMENT, Item::LINK_STATUS_PENDING_PAYMENT],
            'payment review' => [Order::STATE_PAYMENT_REVIEW, Item::LINK_STATUS_PAYMENT_REVIEW],
            'holded' => [Order::STATE_HOLDED, Item::LINK_STATUS_PENDING],
        ];
    }

    /**
     * @param string $state
     * @return Order
     */
    private function saveNewOrderWithDownloadableItemInState(string $state): Order
    {
        $billingAddress = $this->objectManager->create(
            Address::class,
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
                ],
            ]
        );
        $billingAddress->setAddressType('billing');

        $payment = $this->objectManager->create(Payment::class);
        $payment->setMethod('checkmo');

        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('downloadable-product');
        $link = $product->getExtensionAttributes()->getDownloadableProductLinks()[0];

        /** @var OrderItem $orderItem */
        $orderItem = $this->objectManager->create(OrderItem::class);
        $orderItem->setProductId($product->getId())
            ->setProductType(Type::TYPE_DOWNLOADABLE)
            ->setProductOptions(['links' => [$link->getId()]])
            ->setBasePrice(100)
            ->setQtyOrdered(1);

        /** @var Order $order */
        $order = $this->objectManager->create(Order::class);
        $order->setCustomerEmail('mail@to.co')
            ->addItem($orderItem)
            ->setIncrementId('100000041230')
            ->setCustomerIsGuest(true)
            ->setStoreId(1)
            ->setEmailSent(1)
            ->setState($state)
            ->setStatus($state)
            ->setBillingAddress($billingAddress)
            ->setPayment($payment);

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $orderRepository->save($order);

        return $order;
    }
}
