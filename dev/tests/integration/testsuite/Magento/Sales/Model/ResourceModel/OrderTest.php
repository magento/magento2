<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel;

class OrderTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->resourceModel = $this->objectManager->create(\Magento\Sales\Model\ResourceModel\Order::class);
        $this->orderIncrementId = '100000001';
    }

    protected function tearDown()
    {
        $registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->loadByIncrementId($this->orderIncrementId);
        $order->delete();

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);

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
}
