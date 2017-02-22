<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;

/**
 * Order item gift message repository object.
 */
class OrderItemRepository implements \Magento\GiftMessage\Api\OrderItemRepositoryInterface
{
    /**
     * Order factory.
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Store manager interface.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Gift message save model.
     *
     * @var \Magento\GiftMessage\Model\Save
     */
    protected $giftMessageSaveModel;

    /**
     * Message helper.
     *
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $helper;

    /**
     * Message factory.
     *
     * @var \Magento\GiftMessage\Model\MessageFactory
     */
    protected $messageFactory;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\GiftMessage\Model\Save $giftMessageSaveModel
     * @param \Magento\GiftMessage\Helper\Message $helper
     * @param MessageFactory $messageFactory
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
            throw new InvalidTransitionException(__('Gift Messages is not applicable for virtual products'));
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
     */
    protected function getItemById($orderId, $orderItemId)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->orderFactory->create()->load($orderId);
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
        foreach ($order->getItems() as $item) {
            if ($item->getItemId() === $orderItemId) {
                return $item;
            }
        }
        return false;
    }
}
