<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
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
                __("Either no order exists with this ID or gift message isn't allowed.")
            );
        }

        $messageId = $order->getGiftMessageId();
        if (!$messageId) {
            throw new NoSuchEntityException(
                __('No item with the provided ID was found in the Order. Verify the ID and try again.')
            );
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
            throw new NoSuchEntityException(__('No order exists with this ID. Verify your information and try again.'));
        };

        if (0 == $order->getTotalItemCount()) {
            throw new InputException(
                __("Gift messages can't be used for an empty order. Create an order, add an item, and try again.")
            );
        }

        if ($order->getIsVirtual()) {
            throw new InvalidTransitionException(__("Gift messages can't be used for virtual products."));
        }
        if (!$this->helper->isMessagesAllowed('order', $order, $this->storeManager->getStore())) {
            throw new CouldNotSaveException(__("The gift message isn't available."));
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
            throw new CouldNotSaveException(
                __('The gift message couldn\'t be added to the "%1" order.', $e->getMessage()),
                $e
            );
        }
        return true;
    }
}
