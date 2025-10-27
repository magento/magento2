<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Cart\CustomerCartResolver;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\QuoteGraphQl\Model\Cart\MergeCarts\CartQuantityValidatorInterface;

class MergeCarts implements ResolverInterface
{
    /**
     * MergeCarts Constructor
     *
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     * @param CustomerCartResolver $customerCartResolver
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     * @param CartQuantityValidatorInterface $cartQuantityValidator
     * @param array $fields
     */
    public function __construct(
        private readonly GetCartForUser $getCartForUser,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CustomerCartResolver $customerCartResolver,
        private readonly QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId,
        private readonly CartQuantityValidatorInterface $cartQuantityValidator,
        private readonly array $fields
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        if (empty($args['source_cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "source_cart_id" is missing'));
        }

        if (isset($args['destination_cart_id']) && empty($args['destination_cart_id'])) {
            throw new GraphQlInputException(__('The parameter "destination_cart_id" cannot be empty'));
        }

        /** @var ContextInterface $context */
        if (!$context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $currentUserId = $context->getUserId();
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $guestMaskedCartId = $args['source_cart_id'];

        // Resolve destination cart ID
        $customerMaskedCartId = $args['destination_cart_id'] ?? null;

        if (!$customerMaskedCartId) {
            try {
                $cart = $this->customerCartResolver->resolve($currentUserId);
                $customerMaskedCartId = $this->quoteIdToMaskedQuoteId->execute((int) $cart->getId());
            } catch (CouldNotSaveException $exception) {
                throw new GraphQlNoSuchEntityException(
                    __('Could not create empty cart for customer'),
                    $exception
                );
            }
        }

        // Fetch guest and customer carts
        $customerCart = $this->getCartForUser->execute($customerMaskedCartId, $currentUserId, $storeId);
        $guestCart = $this->getCartForUser->execute($guestMaskedCartId, null, $storeId);

        // Validate cart quantities before merging and reload cart before cart merge
        if ($this->cartQuantityValidator->validateFinalCartQuantities($customerCart, $guestCart)) {
            $guestCart = $this->getCartForUser->execute($guestMaskedCartId, null, $storeId);
            $customerCart = $this->getCartForUser->execute($customerMaskedCartId, $currentUserId, $storeId);
        }

        // Merge carts and save
        $customerCart->merge($guestCart);
        $guestCart->setIsActive(false);
        // Check and update gift options from guest cart to customer cart
        $customerCart = $this->updateGiftOptions($guestCart, $customerCart);

        $this->cartRepository->save($customerCart);
        $this->cartRepository->save($guestCart);

        return ['model' => $customerCart];
    }

    /**
     * Check and update gift options in customer cart from guest cart
     *
     * @param Quote $guestCart
     * @param Quote $customerCart
     * @return Quote
     */
    private function updateGiftOptions(Quote $guestCart, Quote $customerCart): Quote
    {
        foreach ($this->fields as $field) {
            if (!empty($guestCart->getData($field)) && empty($customerCart->getData($field))) {
                $customerCart->setData($field, $guestCart->getData($field));
            }
        }

        return $customerCart;
    }
}
