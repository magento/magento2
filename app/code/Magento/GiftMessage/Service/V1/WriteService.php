<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Service\V1;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;

/**
 * Gift message write service data object.
 */
class WriteService implements WriteServiceInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * Store manager interface.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Gift message manager.
     *
     * @var \Magento\GiftMessage\Model\GiftMessageManager
     */
    protected $giftMessageManager;

    /**
     * Message helper.
     *
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $helper;

    /**
     * Constructs a gift message write service data object.
     *
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository Quote repository.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager Store manager.
     * @param \Magento\GiftMessage\Model\GiftMessageManager $giftMessageManager Gift message manager.
     * @param \Magento\GiftMessage\Helper\Message $helper Message helper.
     */
    public function __construct(
        \Magento\Sales\Model\QuoteRepository $quoteRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\GiftMessage\Model\GiftMessageManager $giftMessageManager,
        \Magento\GiftMessage\Helper\Message $helper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->giftMessageManager = $giftMessageManager;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The shopping cart ID.
     * @param Data\Message $giftMessage The gift message.
     * @return bool
     * @throws \Magento\Framework\Exception\InputException You cannot add gift messages to empty carts.
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException You cannot add gift messages to virtual
     * products.
     */
    public function setForQuote($cartId, \Magento\GiftMessage\Service\V1\Data\Message $giftMessage)
    {
        /**
         * Quote.
         *
         * @var \Magento\Sales\Model\Quote $quote
         */
        $quote = $this->quoteRepository->getActive($cartId);

        if (0 == $quote->getItemsCount()) {
            throw new InputException('Gift Messages is not applicable for empty cart');
        }

        if ($quote->isVirtual()) {
            throw new InvalidTransitionException('Gift Messages is not applicable for virtual products');
        }

        $this->setMessage($quote, 'quote', $giftMessage);
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @param int $cartId The shopping cart ID.
     * @param Data\Message $giftMessage The gift message.
     * @param int $itemId The item ID.
     * @return bool
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException You cannot add gift messages to
     * virtual products.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified item does not exist in the cart.
     */
    public function setForItem($cartId, \Magento\GiftMessage\Service\V1\Data\Message $giftMessage, $itemId)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        if (!$item = $quote->getItemById($itemId)) {
            throw new NoSuchEntityException("There is no product with provided  itemId: $itemId in the cart");
        };

        if ($item->getIsVirtual()) {
            throw new InvalidTransitionException('Gift Messages is not applicable for virtual products');
        }

        $this->setMessage($quote, 'quote_item', $giftMessage, $itemId);
        return true;
    }

    /**
     * Sets the gift message to item or quote.
     *
     * @param \Magento\Sales\Model\Quote $quote The quote.
     * @param string $type The type.
     * @param \Magento\GiftMessage\Service\V1\Data\Message $giftMessage The gift message.
     * @param null|int $entityId The entity ID.
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException The specified gift message is not available.
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException The billing or shipping address is not set.
     */
    protected function setMessage(\Magento\Sales\Model\Quote $quote, $type, $giftMessage, $entityId = null)
    {
        if (is_null($quote->getBillingAddress()->getCountryId())) {
            throw new InvalidTransitionException('Billing address is not set');
        }

        // check if shipping address is set
        if (is_null($quote->getShippingAddress()->getCountryId())) {
            throw new InvalidTransitionException('Shipping address is not set');
        }

        $configType = $type == 'quote' ? '' : 'items';
        if (!$this->helper->getIsMessagesAvailable($configType, $quote, $this->storeManager->getStore())) {
            throw new CouldNotSaveException('Gift Message is not available');
        }
        $message[$type][$entityId] = [
            'from' => $giftMessage->getSender(),
            'to' => $giftMessage->getRecipient(),
            'message' => $giftMessage->getMessage(),
        ];

        try {
            $this->giftMessageManager->add($message, $quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException('Could not add gift message to shopping cart');
        }
    }
}
