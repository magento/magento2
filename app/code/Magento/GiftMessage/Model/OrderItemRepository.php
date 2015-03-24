<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
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
    public function get($orderId, $itemId)
    {
        if (!$item = $this->getItemById($orderId, $itemId)) {
            return null;
        };

        if (!$this->helper->getIsMessagesAvailable('order_item', $item, $this->storeManager->getStore())) {
            return null;
        }

        $messageId = $item->getGiftMessageId();
        if (!$messageId) {
            return null;
        }

        return $this->messageFactory->create()->load($messageId);
    }

    /**
     * {@inheritDoc}
     */
    public function save($orderId, $itemId, \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->orderFactory->create()->load($orderId);

        /** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
        if (!$item = $this->getItemById($orderId, $itemId)) {
            throw new NoSuchEntityException(__('There is no item with provided id in the order'));
        };

        if ($order->getIsVirtual()) {
            throw new InvalidTransitionException(__('Gift Messages is not applicable for virtual products'));
        }
        if (!$this->helper->getIsMessagesAvailable('order_item', $item, $this->storeManager->getStore())) {
            throw new CouldNotSaveException(__('Gift Message is not available'));
        }

        $message = [];
        $message[$itemId] = [
            'type' => 'order_item',
            'sender' => $giftMessage->getSender(),
            'recipient' => $giftMessage->getRecipient(),
            'message' => $giftMessage->getMessage(),
        ];

        $this->giftMessageSaveModel->setGiftmessages($message);
        try {
            $this->giftMessageSaveModel->saveAllInOrder();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not add gift message to order'));
        }
        return true;
    }

    /**
     * Get order item by id
     *
     * @param int $orderId
     * @param int $itemId
     * @return \Magento\Sales\Api\Data\OrderItemInterface|bool
     */
    protected function getItemById($orderId, $itemId)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->orderFactory->create()->load($orderId);
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $item */
        foreach ($order->getItems() as $item) {
            if ($item->getItemId() === $itemId) {
                return $item;
            }
        }
        return false;
    }
}
