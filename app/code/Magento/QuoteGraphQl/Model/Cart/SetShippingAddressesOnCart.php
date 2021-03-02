<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\QuoteGraphQl\Model\Cart\Address\SaveQuoteAddressToCustomerAddressBook;

/**
 * Set single shipping address for a specified shopping cart
 */
class SetShippingAddressesOnCart implements SetShippingAddressesOnCartInterface
{
    /**
     * @var QuoteAddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var AssignShippingAddressToCart
     */
    private $assignShippingAddressToCart;

    /**
     * @var SaveQuoteAddressToCustomerAddressBook
     */
    private $saveQuoteAddressToCustomerAddressBook;

    /**
     * @var GetShippingAddress
     */
    private $getShippingAddress;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @param QuoteAddressFactory $quoteAddressFactory
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param GetCartForUser $getCartForUser
     * @param AssignShippingAddressToCart $assignShippingAddressToCart
     * @param GetShippingAddress $getShippingAddress
     * @param SaveQuoteAddressToCustomerAddressBook|null $saveQuoteAddressToCustomerAddressBook
     * @param QuoteRepository|null $quoteRepository
     */
    public function __construct(
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        GetCartForUser $getCartForUser,
        AssignShippingAddressToCart $assignShippingAddressToCart,
        GetShippingAddress $getShippingAddress,
        QuoteAddressFactory $quoteAddressFactory = null,
        SaveQuoteAddressToCustomerAddressBook $saveQuoteAddressToCustomerAddressBook = null,
        QuoteRepository $quoteRepository = null
    ) {
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->getCartForUser = $getCartForUser;
        $this->assignShippingAddressToCart = $assignShippingAddressToCart;
        $this->getShippingAddress = $getShippingAddress;
        $this->quoteAddressFactory = $quoteAddressFactory
            ?? ObjectManager::getInstance()->get(QuoteAddressFactory::class);
        $this->quoteRepository = $quoteRepository
            ?? ObjectManager::getInstance()->get(QuoteRepository::class);
        $this->saveQuoteAddressToCustomerAddressBook = $saveQuoteAddressToCustomerAddressBook
            ?? ObjectManager::getInstance()->get(SaveQuoteAddressToCustomerAddressBook::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(ContextInterface $context, CartInterface $cart, array $shippingAddressesInput): void
    {
        if (count($shippingAddressesInput) > 1) {
            throw new GraphQlInputException(
                __('You cannot specify multiple shipping addresses.')
            );
        }
        $shippingAddressInput = current($shippingAddressesInput) ?? [];
        $customerAddressId = $shippingAddressInput['customer_address_id'] ?? null;

        if (!$customerAddressId
            && isset($shippingAddressInput['address'])
            && !isset($shippingAddressInput['address']['save_in_address_book'])
        ) {
            $shippingAddressInput['address']['save_in_address_book'] = true;
        }

        $shippingAddress = $this->getShippingAddress->execute($context, $shippingAddressInput);

        $errors = $shippingAddress->validate();

        if (true !== $errors) {
            $e = new GraphQlInputException(__('Shipping address errors'));
            foreach ($errors as $error) {
                $e->addError(new GraphQlInputException($error));
            }
            throw $e;
        }
        $customerId = $context->getUserId();

        if (null === $customerAddressId) {
            $shippingAddress = $this->quoteAddressFactory->createBasedOnInputData($shippingAddressInput);

            // need to save address only for registered user and if save_in_address_book = true
            if (0 !== $customerId && !empty($addressInput['save_in_address_book'])) {
                $this->saveQuoteAddressToCustomerAddressBook->execute($shippingAddress, $customerId);
            }
        } else {
            if (false === $context->getExtensionAttributes()->getIsCustomer()) {
                throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
            }

            $shippingAddress = $this->quoteAddressFactory->createBasedOnCustomerAddress(
                (int)$customerAddressId,
                $customerId
            );
        }
        $this->assignShippingAddressToCart->execute($cart, $shippingAddress);

        // reload updated cart & trigger quote re-evaluation after address change
        $maskedId = $this->quoteIdToMaskedQuoteId->execute((int)$cart->getId());
        $cart = $this->getCartForUser->execute($maskedId, $context->getUserId(), $cart->getStoreId());
        $this->quoteRepository->save($cart);
    }
}
