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
 * Shopping cart gift message item repository object for registered customer
 * @since 2.0.0
 */
class ItemRepository implements \Magento\GiftMessage\Api\ItemRepositoryInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     * @since 2.0.0
     */
    protected $quoteRepository;

    /**
     * Store manager interface.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * Gift message manager.
     *
     * @var \Magento\GiftMessage\Model\GiftMessageManager
     * @since 2.0.0
     */
    protected $giftMessageManager;

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
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param GiftMessageManager $giftMessageManager
     * @param \Magento\GiftMessage\Helper\Message $helper
     * @param MessageFactory $messageFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\GiftMessage\Model\GiftMessageManager $giftMessageManager,
        \Magento\GiftMessage\Helper\Message $helper,
        \Magento\GiftMessage\Model\MessageFactory $messageFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->giftMessageManager = $giftMessageManager;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->messageFactory = $messageFactory;
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function get($cartId, $itemId)
    {
        /**
         * Quote.
         *
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$item = $quote->getItemById($itemId)) {
            throw new NoSuchEntityException(__('There is no item with provided id in the cart'));
        };
        $messageId = $item->getGiftMessageId();
        if (!$messageId) {
            return null;
        }

        /**
         * Model.
         *
         * @var \Magento\GiftMessage\Model\Message $model
         */
        return $this->messageFactory->create()->load($messageId);
    }

    /**
     * {@inheritDoc}
     * @since 2.0.0
     */
    public function save($cartId, \Magento\GiftMessage\Api\Data\MessageInterface $giftMessage, $itemId)
    {
        /**
         * Quote.
         *
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);

        if (!$item = $quote->getItemById($itemId)) {
            throw new NoSuchEntityException(
                __('There is no product with provided  itemId: %1 in the cart', $itemId)
            );
        };

        if ($item->getIsVirtual()) {
            throw new InvalidTransitionException(__('Gift Messages are not applicable for virtual products'));
        }
        $messageText = $giftMessage->getMessage();
        if ($messageText && !$this->helper->isMessagesAllowed('items', $quote, $this->storeManager->getStore())) {
            throw new CouldNotSaveException(__('Gift Message is not available'));
        }
        $this->giftMessageManager->setMessage($quote, 'quote_item', $giftMessage, $itemId);
        return true;
    }
}
