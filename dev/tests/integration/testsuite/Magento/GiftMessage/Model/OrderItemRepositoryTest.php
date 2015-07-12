<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model;

class OrderItemRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    /** @var \Magento\GiftMessage\Model\Message */
    protected $message;

    /** @var \Magento\GiftMessage\Model\OrderItemRepository */
    protected $giftMessageOrderItemRepository;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->message = $this->objectManager->create('Magento\GiftMessage\Model\Message');
        $this->message->setSender('Romeo');
        $this->message->setRecipient('Mercutio');
        $this->message->setMessage('I thought all for the best.');

        $this->giftMessageOrderItemRepository = $this->objectManager->create(
            'Magento\GiftMessage\Model\OrderItemRepository'
        );

    }

    protected function tearDown()
    {
        unset($this->objectManager);
        unset($this->message);
        unset($this->giftMessageOrderItemRepository);
    }

    /**
     * @magentoDataFixture Magento/GiftMessage/_files/order_with_message.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     */
    public function testGet()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId('100000001');
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        $orderItem = $order->getItems();
        $orderItem = array_shift($orderItem);

        /** @var \Magento\GiftMessage\Api\Data\MessageInterface $message */
        $message = $this->giftMessageOrderItemRepository->get($order->getEntityId(), $orderItem->getItemId());
        $this->assertEquals('Romeo', $message->getSender());
        $this->assertEquals('Mercutio', $message->getRecipient());
        $this->assertEquals('I thought all for the best.', $message->getMessage());
    }

    /**
     * @magentoDataFixture Magento/GiftMessage/_files/order_with_message.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no item with provided id in the order
     */
    public function testGetNoProvidedItemId()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId('100000001');
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        $orderItem = $order->getItems();
        $orderItem = array_shift($orderItem);

        /** @var \Magento\GiftMessage\Api\Data\MessageInterface $message */
        $this->giftMessageOrderItemRepository->get($order->getEntityId(), $orderItem->getItemId() * 10);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     */
    public function testSave()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId('100000001');
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        $orderItem = $order->getItems();
        $orderItem = array_shift($orderItem);

        /** @var \Magento\GiftMessage\Api\Data\MessageInterface $message */
        $result = $this->giftMessageOrderItemRepository->save(
            $order->getEntityId(),
            $orderItem->getItemId(),
            $this->message
        );

        $message = $this->giftMessageOrderItemRepository->get($order->getEntityId(), $orderItem->getItemId());

        $this->assertTrue($result);
        $this->assertEquals('Romeo', $message->getSender());
        $this->assertEquals('Mercutio', $message->getRecipient());
        $this->assertEquals('I thought all for the best.', $message->getMessage());
    }


    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 0
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Gift Message is not available
     */
    public function testSaveMessageIsNotAvailable()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId('100000001');
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        $orderItem = $order->getItems();
        $orderItem = array_shift($orderItem);

        /** @var \Magento\GiftMessage\Api\Data\MessageInterface $message */
        $this->giftMessageOrderItemRepository->save($order->getEntityId(), $orderItem->getItemId(), $this->message);
    }

    /**
     * @magentoDataFixture Magento/GiftMessage/_files/virtual_order.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Gift Messages is not applicable for virtual products
     */
    public function testSaveMessageIsVirtual()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId('100000001');
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        $orderItem = $order->getItems();
        $orderItem = array_shift($orderItem);

        /** @var \Magento\GiftMessage\Api\Data\MessageInterface $message */
        $this->giftMessageOrderItemRepository->save($order->getEntityId(), $orderItem->getItemId(), $this->message);
    }

    /**
     * @magentoDataFixture Magento/GiftMessage/_files/empty_order.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     * @expectedException  \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage There is no item with provided id in the order
     */
    public function testSaveMessageNoProvidedItemId()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId('100000001');
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        $orderItem = $order->getItems();
        $orderItem = array_shift($orderItem);

        /** @var \Magento\GiftMessage\Api\Data\MessageInterface $message */
        $this->giftMessageOrderItemRepository->save(
            $order->getEntityId(),
            $orderItem->getItemId() * 10,
            $this->message
        );
    }
}
