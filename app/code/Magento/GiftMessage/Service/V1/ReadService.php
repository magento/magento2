<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Service\V1;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Shopping cart gift message service object.
 */
class ReadService implements ReadServiceInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Message factory.
     *
     * @var \Magento\GiftMessage\Model\MessageFactory
     */
    protected $messageFactory;

    /**
     * Message mapper.
     *
     * @var \Magento\GiftMessage\Service\V1\Data\MessageMapper
     */
    protected $messageMapper;

    /**
     * Constructs a shopping cart gift message service object.
     *
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository Quote repository.
     * @param \Magento\GiftMessage\Model\MessageFactory $messageFactory Message factory.
     * @param \Magento\GiftMessage\Service\V1\Data\MessageMapper $messageMapper Message mapper.
     */
    public function __construct(
        \Magento\Sales\Model\QuoteRepository $quoteRepository,
        \Magento\GiftMessage\Model\MessageFactory $messageFactory,
        \Magento\GiftMessage\Service\V1\Data\MessageMapper $messageMapper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->messageFactory = $messageFactory;
        $this->messageMapper = $messageMapper;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The shopping cart ID.
     * @return \Magento\GiftMessage\Service\V1\Data\Message Gift message.
     */
    public function get($cartId)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $messageId = $quote->getGiftMessageId();
        if (!$messageId) {
            return null;
        }

        /** @var \Magento\GiftMessage\Model\Message $model */
        $model = $this->messageFactory->create()->load($messageId);

        return $this->messageMapper->extractDto($model);
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The shopping cart ID.
     * @param int $itemId The item ID.
     * @return \Magento\GiftMessage\Service\V1\Data\Message Gift message.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item does not exist in the cart.
     */
    public function getItemMessage($cartId, $itemId)
    {
        /**
         * Quote.
         *
         * @var \Magento\Sales\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$item = $quote->getItemById($itemId)) {
            throw new NoSuchEntityException('There is no item with provided id in the cart');
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
        $model = $this->messageFactory->create()->load($messageId);

        return $this->messageMapper->extractDto($model);
    }
}
