<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;

/**
 * Order item gift message repository object.
 * @since 2.0.0
 */
class OrderItemRepository implements \Magento\GiftMessage\Api\OrderItemRepositoryInterface
{
    /**
     * Order factory.
     *
     * @var \Magento\Sales\Model\OrderFactory
     * @since 2.0.0
     */
    protected $orderFactory;

    /**
     * Cached orders data.
     *
     * @var \Magento\Sales\Api\Data\OrderInterface[]
     * @since 2.2.0
     */
    private $orders;

    /**
     * Store manager interface.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * Gift message save model.
     *
     * @var \Magento\GiftMessage\Model\Save
     * @since 2.0.0
     */
    protected $giftMessageSaveModel;

    /**
     * Message helper.
     *
     * @var \Magento\GiftMessage\Helper\Message
     * @since 2.0.0
     */
    protected $helper;

    /**
     * Message factory.
     *
     * @var \Magento\GiftMessage\Model\MessageFactory
     * @since 2.0.0
     */
    protected $messageFactory;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\GiftMessage\Model\Save $giftMessageSaveModel
     * @param \Magento\GiftMessage\Helper\Message $helper
     * @param MessageFactory $messageFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\GiftMessage\Model\Save $giftMessageSaveModel,
        \Magento\GiftMessage\Helper\Message $helper,
        \Magento\GiftMessage\Model\MessageFactory $messageFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->giftMessageSaveModel = $giftMessageSaveModel;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->messageFactory = $messageFactory;
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function get($orderId, $orderItemId)
    {
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        if (!$orderItem = $this->getItemById($orderId, $orderItemId)) {
            throw new NoSuchEntityException(__('There is no item with provided id in the order'));
        };

        if (!$this->helper->isMessagesAllowed('order_item', $orderItem, $this->storeManager->getStore())) {
            throw new NoSuchEntityException(
                __('There is no item with provided id in the order or gift message isn\'t allowed')
            );
        }

        $messageId = $orderItem->getGiftMessageId();
        if (!$messageId) {
            throw new NoSuchEntityException(__('There is no item with provided id in the order'));
        }

        return $this->messageFactory->create()->load($messageId);
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function save($orderId, $orderItemId, \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->orderFactory->create()->load($orderId);

        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        if (!$orderItem = $this->getItemById($orderId, $orderItemId)) {
            throw new NoSuchEntityException(__('There is no item with provided id in the order'));
        };

        if ($order->getIsVirtual()) {
            throw new InvalidTransitionException(__('Gift Messages are not applicable for virtual products'));
        }
        if (!$this->helper->isMessagesAllowed('order_item', $orderItem, $this->storeManager->getStore())) {
            throw new CouldNotSaveException(__('Gift Message is not available'));
        }

        $message = [];
        $message[$orderItemId] = [
            'type' => 'order_item',
            'sender' => $giftMessage->getSender(),
            'recipient' => $giftMessage->getRecipient(),
            'message' => $giftMessage->getMessage(),
        ];

        $this->giftMessageSaveModel->setGiftmessages($message);
        try {
            $this->giftMessageSaveModel->saveAllInOrder();
            unset($this->orders[$orderId]);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not add gift message to order: "%1"', $e->getMessage()), $e);
        }
        return true;
    }

    /**
     * Get order item by id
     *
     * @param int $orderId
     * @param int $orderItemId
     * @return \Magento\Sales\Api\Data\OrderItemInterface|bool
     * @since 2.0.0
     */
    protected function getItemById($orderId, $orderItemId)
    {
        if (!isset($this->orders[$orderId])) {
            $this->orders[$orderId] = $this->orderFactory->create()->load($orderId);
        }
        /** @var \Magento\Sales\Api\Data\OrderInterface $item */
        $order = $this->orders[$orderId];
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
        $item = $order->getItemById($orderItemId);

        if ($item !== null) {
            return $item;
        }
        return false;
    }
}
