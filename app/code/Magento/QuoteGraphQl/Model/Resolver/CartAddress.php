<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteId;
use Magento\QuoteGraphQl\Model\Authorization\IsCartMutationAllowedForCurrentUser;
use Magento\QuoteGraphQl\Model\Resolver\Address\AddressDataProvider;

/**
 * @inheritdoc
 */
class CartAddress implements ResolverInterface
{
    /**
     * @var AddressDataProvider
     */
    private $addressDataProvider;

    /**
     * @var IsCartMutationAllowedForCurrentUser
     */
    private $isCartMutationAllowedForCurrentUser;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteId
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * CartAddress constructor.
     *
     * @param MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId
     * @param CartRepositoryInterface $cartRepository
     * @param AddressDataProvider $addressDataProvider
     * @param IsCartMutationAllowedForCurrentUser $isCartMutationAllowedForCurrentUser
     */
    public function __construct(
        MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId,
        CartRepositoryInterface $cartRepository,
        AddressDataProvider $addressDataProvider,
        IsCartMutationAllowedForCurrentUser $isCartMutationAllowedForCurrentUser
    ) {
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->cartRepository = $cartRepository;
        $this->addressDataProvider = $addressDataProvider;
        $this->isCartMutationAllowedForCurrentUser = $isCartMutationAllowedForCurrentUser;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /* The cart_id is used instead of the model because some parent resolvers do not work
           with cart model */
        if (!isset($value['cart_id'])) {
            throw new LocalizedException(__('"cart_id" value should be specified'));
        }

        $maskedCartId = $value['cart_id'];

        try {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart with ID "%masked_cart_id"', ['masked_cart_id' => $maskedCartId])
            );
        }

        if (false === $this->isCartMutationAllowedForCurrentUser->execute($quoteId)) {
            throw new GraphQlAuthorizationException(
                __(
                    'The current user cannot perform operations on cart "%masked_cart_id"',
                    ['masked_cart_id' => $maskedCartId]
                )
            );
        }

        try {
            $quote = $this->cartRepository->get($quoteId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart with ID "%quote_id"', ['quote_id' => $quoteId])
            );
        }

        return $this->addressDataProvider->getCartAddresses($quote);
    }
}
