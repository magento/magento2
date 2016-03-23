<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Order gift message repository object.
 */
class OrderRepository implements \Magento\GiftMessage\Api\OrderRepositoryInterface
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
    public function get($orderId)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->orderFactory->create()->load($orderId);

        if (!$this->helper->isMessagesAllowed('order', $order, $this->storeManager->getStore())) {
            throw new NoSuchEntityException(
                __('There is no order with provided id or gift message isn\'t allowed')
            );
        }

        $messageId = $order->getGiftMessageId();
        if (!$messageId) {
            throw new NoSuchEntityException(__('There is no item with provided id in the order'));
        }

        return $this->messageFactory->create()->load($messageId);
    }

    /**
     * {@inheritDoc}
     */
    public function save($orderId, \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->orderFactory->create()->load($orderId);
        if (!$order->getEntityId()) {
            throw new NoSuchEntityException(__('There is no order with provided id'));
        };

        if (0 == $order->getTotalItemCount()) {
            throw new InputException(__('Gift Messages are not applicable for empty order'));
        }

        if ($order->getIsVirtual()) {
            throw new InvalidTransitionException(__('Gift Messages are not applicable for virtual products'));
        }
        if (!$this->helper->isMessagesAllowed('order', $order, $this->storeManager->getStore())) {
            throw new CouldNotSaveException(__('Gift Message is not available'));
        }

        $message = [];
        $message[$orderId] = [
            'type' => 'order',
            $giftMessage::CUSTOMER_ID => $giftMessage->getCustomerId(),
            $giftMessage::SENDER => $giftMessage->getSender(),
            $giftMessage::RECIPIENT => $giftMessage->getRecipient(),
            $giftMessage::MESSAGE => $giftMessage->getMessage(),
        ];

        $this->giftMessageSaveModel->setGiftmessages($message);
        try {
            $this->giftMessageSaveModel->saveAllInOrder();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not add gift message to order: "%1"', $e->getMessage()), $e);
        }
        return true;
    }
}
