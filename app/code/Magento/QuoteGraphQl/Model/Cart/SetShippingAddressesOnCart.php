<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\QuoteRepository;

/**
 * Set single shipping address for a specified shopping cart
 */
class SetShippingAddressesOnCart implements SetShippingAddressesOnCartInterface
{
    /**
     * SetShippingAddressesOnCart Constructor
     *
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param GetCartForUser $getCartForUser
     * @param AssignShippingAddressToCart $assignShippingAddressToCart
     * @param GetShippingAddress $getShippingAddress
     * @param QuoteRepository $quoteRepository
     * @param Uid $uidEncoder
     */
    public function __construct(
        private readonly QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        private readonly GetCartForUser                  $getCartForUser,
        private readonly AssignShippingAddressToCart     $assignShippingAddressToCart,
        private readonly GetShippingAddress              $getShippingAddress,
        private readonly QuoteRepository                 $quoteRepository,
        private readonly Uid                             $uidEncoder
    ) {
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

        if (isset($shippingAddressInput['customer_address_uid'])) {
            $shippingAddressInput['customer_address_id'] = (int) $this->uidEncoder->decode(
                (string) $shippingAddressInput['customer_address_uid']
            );
            unset($shippingAddressInput['customer_address_uid']);
        }

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
        $this->assignShippingAddressToCart->execute($cart, $shippingAddress);

        // reload updated cart & trigger quote re-evaluation after address change
        $maskedId = $this->quoteIdToMaskedQuoteId->execute((int)$cart->getId());
        $cart = $this->getCartForUser->execute($maskedId, $context->getUserId(), $cart->getStoreId());
        $this->quoteRepository->save($cart);
    }
}
