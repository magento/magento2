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
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Order item gift message repository object.
 */
class OrderItemRepository implements \Magento\GiftMessage\Api\OrderItemRepositoryInterface, ResetAfterRequestInterface
{
    /**
     * Factory for Order instances.
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Model for Gift message save.
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
     * @inheritdoc
     */
    public function getByOrder(OrderInterface $order, int $orderItemId)
    {
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        if (!$orderItem = $order->getItemById($orderItemId)) {
            throw new NoSuchEntityException(
                __('No item with the provided ID was found in the Order. Verify the ID and try again.')
            );
        }

        if (!$this->helper->isMessagesAllowed('order_item', $orderItem, $this->storeManager->getStore())) {
            throw new NoSuchEntityException(
                __(
                    "No item with the provided ID was found in the Order, or a gift message isn't allowed. "
                    . "Verify and try again."
                )
            );
        }

        $messageId = $orderItem->getGiftMessageId();
        if (!$messageId) {
            throw new NoSuchEntityException(
                __('No item with the provided ID was found in the Order. Verify the ID and try again.')
            );
        }

        return $this->messageFactory->create()->load($messageId);
    }

    /**
     * @inheritdoc
     */
    public function get($orderId, $orderItemId)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->orderFactory->create()->load($orderId);
        return $this->getByOrder($order, (int) $orderItemId);
    }

    /**
     * @inheritdoc
     */
    public function saveForOrder(
        OrderInterface $order,
        int $orderItemId,
        \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage
    ) {
        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
        if (!$orderItem = $order->getItemById($orderItemId)) {
            throw new NoSuchEntityException(
                __('No item with the provided ID was found in the Order. Verify the ID and try again.')
            );
        }

        if ($order->getIsVirtual()) {
            throw new InvalidTransitionException(__("Gift messages can't be used for virtual products."));
        }
        if (!$this->helper->isMessagesAllowed('order_item', $orderItem, $this->storeManager->getStore())) {
            throw new CouldNotSaveException(__("The gift message isn't available."));
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
            throw new CouldNotSaveException(
                __('The gift message couldn\'t be added to the "%1" order.', $e->getMessage()),
                $e
            );
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function save($orderId, $orderItemId, \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->orderFactory->create()->load($orderId);

        return $this->saveForOrder($order, (int) $orderItemId, $giftMessage);
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->orders = null;
    }
}
