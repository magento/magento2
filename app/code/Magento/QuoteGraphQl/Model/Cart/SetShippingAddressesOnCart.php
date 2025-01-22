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

/**
 * Set single shipping address for a specified shopping cart
 */
class SetShippingAddressesOnCart implements SetShippingAddressesOnCartInterface
{
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
     * @var GetShippingAddress
     */
    private $getShippingAddress;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param GetCartForUser $getCartForUser
     * @param AssignShippingAddressToCart $assignShippingAddressToCart
     * @param GetShippingAddress $getShippingAddress
     * @param QuoteRepository|null $quoteRepository
     */
    public function __construct(
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        GetCartForUser $getCartForUser,
        AssignShippingAddressToCart $assignShippingAddressToCart,
        GetShippingAddress $getShippingAddress,
        ?QuoteRepository $quoteRepository = null
    ) {
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
        $this->getCartForUser = $getCartForUser;
        $this->assignShippingAddressToCart = $assignShippingAddressToCart;
        $this->getShippingAddress = $getShippingAddress;
        $this->quoteRepository = $quoteRepository
            ?? ObjectManager::getInstance()->get(QuoteRepository::class);
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
        $this->assignShippingAddressToCart->execute($cart, $shippingAddress);

        // reload updated cart & trigger quote re-evaluation after address change
        $maskedId = $this->quoteIdToMaskedQuoteId->execute((int)$cart->getId());
        $cart = $this->getCartForUser->execute($maskedId, $context->getUserId(), $cart->getStoreId());
        $this->quoteRepository->save($cart);
    }
}
