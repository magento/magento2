<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model;

class OrderItemRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $objectManager;

    /** @var \Magento\GiftMessage\Model\Message */
    protected $message;

    /** @var \Magento\GiftMessage\Model\OrderItemRepository */
    protected $giftMessageOrderItemRepository;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->message = $this->objectManager->create(\Magento\GiftMessage\Model\Message::class);
        $this->message->setSender('Romeo');
        $this->message->setRecipient('Mercutio');
        $this->message->setMessage('I thought all for the best.');

        $this->giftMessageOrderItemRepository = $this->objectManager->create(
            \Magento\GiftMessage\Model\OrderItemRepository::class
        );
    }

    protected function tearDown(): void
    {
        $this->objectManager = null;
        $this->message = null;
        $this->giftMessageOrderItemRepository = null;
    }

    /**
     * @magentoDataFixture Magento/GiftMessage/_files/order_with_message.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     */
    public function testGet()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)->loadByIncrementId('100000001');
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
     *
     */
    public function testGetNoProvidedItemId()
    {
        $this->expectExceptionMessage("No item with the provided ID was found in the Order. Verify the ID and try again.");
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)->loadByIncrementId('100000001');
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
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)->loadByIncrementId('100000001');
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
     *
     */
    public function testSaveMessageIsNotAvailable()
    {
        $this->expectExceptionMessage("The gift message isn't available.");
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)->loadByIncrementId('100000001');
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        $orderItem = $order->getItems();
        $orderItem = array_shift($orderItem);

        /** @var \Magento\GiftMessage\Api\Data\MessageInterface $message */
        $this->giftMessageOrderItemRepository->save($order->getEntityId(), $orderItem->getItemId(), $this->message);
    }

    /**
     * @magentoDataFixture Magento/GiftMessage/_files/virtual_order.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     *
     */
    public function testSaveMessageIsVirtual()
    {
        $this->expectExceptionMessage("Gift messages can't be used for virtual products.");
        $this->expectException(\Magento\Framework\Exception\State\InvalidTransitionException::class);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)->loadByIncrementId('100000001');
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        $orderItem = $order->getItems();
        $orderItem = array_shift($orderItem);

        /** @var \Magento\GiftMessage\Api\Data\MessageInterface $message */
        $this->giftMessageOrderItemRepository->save($order->getEntityId(), $orderItem->getItemId(), $this->message);
    }

    /**
     * @magentoDataFixture Magento/GiftMessage/_files/empty_order.php
     * @magentoConfigFixture default_store sales/gift_options/allow_items 1
     *
     */
    public function testSaveMessageNoProvidedItemId()
    {
        $this->expectExceptionMessage("No item with the provided ID was found in the Order. Verify the ID and try again.");
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class)->loadByIncrementId('100000001');
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
