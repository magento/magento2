<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Sales\Model\ResourceModel\Order.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderTest extends TestCase
{
    /**
     * @var Order
     */
    private $resourceModel;

    /**
     * @var int
     */
    private $orderIncrementId;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->resourceModel = $this->objectManager->create(Order::class);
        $this->orderIncrementId = '100000001';
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $registry = $this->objectManager->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        $orderCollection = $this->objectManager->create(OrderCollectionFactory::class)->create();
        foreach ($orderCollection as $order) {
            $order->delete();
        }

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);

        $defaultStore = $this->storeRepository->get('default');
        $this->storeManager->setCurrentStore($defaultStore->getId());
    }

    /**
     * Test save order
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testSaveOrder(): void
    {
        $addressData = [
            'region' => 'CA',
            'postcode' => '11111',
            'lastname' => 'lastname',
            'firstname' => 'firstname',
            'street' => 'street',
            'city' => 'Los Angeles',
            'email' => 'admin@example.com',
            'telephone' => '11111111',
            'country_id' => 'US'
        ];

        $billingAddress = $this->objectManager->create(Address::class, ['data' => $addressData]);
        $billingAddress->setAddressType('billing');

        $shippingAddress = clone $billingAddress;
        $shippingAddress->setId(null)->setAddressType('shipping');

        $payment = $this->objectManager->create(Payment::class);
        $payment->setMethod('checkmo');

        /** @var Item $orderItem */
        $orderItem = $this->objectManager->create(Item::class);
        $orderItem->setProductId(1)
            ->setQtyOrdered(2)
            ->setBasePrice(10)
            ->setPrice(10)
            ->setRowTotal(10);

        /** @var OrderModel $order */
        $order = $this->objectManager->create(OrderModel::class);
        $order->setIncrementId($this->orderIncrementId)
            ->setState(OrderModel::STATE_PROCESSING)
            ->setStatus($order->getConfig()->getStateDefaultStatus(OrderModel::STATE_PROCESSING))
            ->setSubtotal(100)
            ->setBaseSubtotal(100)
            ->setBaseGrandTotal(100)
            ->setCustomerIsGuest(true)
            ->setCustomerEmail('customer@null.com')
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setStoreId(
                $this->objectManager
                    ->get(StoreManagerInterface::class)
                    ->getStore()
                    ->getId()
            )
            ->addItem($orderItem)
            ->setPayment($payment);

        $this->resourceModel->save($order);
        $this->assertNotNull($order->getCreatedAt());
        $this->assertNotNull($order->getUpdatedAt());
    }

    /**
     * Check that store name and x_forwarded_for with length within 255 chars can be saved in table sales_order
     *
     * @magentoDataFixture Magento/Store/_files/store_with_long_name.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testSaveLongNames(): void
    {
        $xForwardedFor = str_repeat('x', 255);

        $store = $this->storeRepository->get('test_2');
        $this->storeManager->setCurrentStore($store->getId());
        $eventManager = $this->objectManager->get(ManagerInterface::class);
        $eventManager->dispatch('store_add', ['store' => $store]);
        $order = $this->objectManager->create(OrderModel::class);
        $payment = $this->objectManager->create(Payment::class);
        $payment->setMethod('checkmo');

        $order->setStoreId($store->getId());
        $order->setXForwardedFor($xForwardedFor);
        $order->setPayment($payment);
        $this->resourceModel->save($order);

        $orderRepository = $this->objectManager->create(OrderRepositoryInterface::class);
        $order = $orderRepository->get($order->getId());

        $this->assertEquals(255, strlen($order->getStoreName()));
        $this->assertEquals(255, strlen($order->getXForwardedFor()));

        $this->assertEquals($xForwardedFor, $order->getXForwardedFor());
        $this->assertStringContainsString($store->getWebsite()->getName(), $order->getStoreName());
        $this->assertStringContainsString($store->getGroup()->getName(), $order->getStoreName());
    }
}
