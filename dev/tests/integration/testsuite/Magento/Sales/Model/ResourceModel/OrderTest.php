<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel;

use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    protected $resourceModel;

    /**
     * @var int
     */
    protected $orderIncrementId;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

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
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->resourceModel = $this->objectManager->create(\Magento\Sales\Model\ResourceModel\Order::class);
        $this->orderIncrementId = '100000001';
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
        $this->storeRepository = $this->objectManager->get(StoreRepositoryInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
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

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSaveOrder()
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

        $billingAddress = $this->objectManager->create(
            \Magento\Sales\Model\Order\Address::class,
            ['data' => $addressData]
        );
        $billingAddress->setAddressType('billing');

        $shippingAddress = clone $billingAddress;
        $shippingAddress->setId(null)->setAddressType('shipping');

        $payment = $this->objectManager->create(\Magento\Sales\Model\Order\Payment::class);
        $payment->setMethod('checkmo');

        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $this->objectManager->create(\Magento\Sales\Model\Order\Item::class);
        $orderItem->setProductId(1)
            ->setQtyOrdered(2)
            ->setBasePrice(10)
            ->setPrice(10)
            ->setRowTotal(10);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->setIncrementId($this->orderIncrementId)
            ->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
            ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING))
            ->setSubtotal(100)
            ->setBaseSubtotal(100)
            ->setBaseGrandTotal(100)
            ->setCustomerIsGuest(true)
            ->setCustomerEmail('customer@null.com')
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setStoreId(
                $this->objectManager
                    ->get(\Magento\Store\Model\StoreManagerInterface::class)
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
     * Check that store name with length within 255 chars can be saved in table sales_order
     *
     * @magentoDataFixture Magento/Store/_files/store_with_long_name.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testSaveStoreName()
    {
        $store = $this->storeRepository->get('test_2');
        $this->storeManager->setCurrentStore($store->getId());
        $eventManager = $this->objectManager->get(ManagerInterface::class);
        $eventManager->dispatch('store_add', ['store' => $store]);
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $payment = $this->objectManager->create(\Magento\Sales\Model\Order\Payment::class);
        $payment->setMethod('checkmo');
        $order->setStoreId($store->getId())->setPayment($payment);
        $this->resourceModel->save($order);
        $orderRepository = $this->objectManager->create(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $order = $orderRepository->get($order->getId());
        $this->assertEquals(255, strlen($order->getStoreName()));
        $this->assertStringContainsString($store->getWebsite()->getName(), $order->getStoreName());
        $this->assertStringContainsString($store->getGroup()->getName(), $order->getStoreName());
    }
}
