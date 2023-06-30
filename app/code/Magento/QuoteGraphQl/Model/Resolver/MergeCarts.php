<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\App\ObjectManager;
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
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * Merge Carts Resolver
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class MergeCarts implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CustomerCartResolver
     */
    private $customerCartResolver;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private $quoteIdToMaskedQuoteId;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartRepositoryInterface $cartRepository
     * @param CustomerCartResolver|null $customerCartResolver
     * @param QuoteIdToMaskedQuoteIdInterface|null $quoteIdToMaskedQuoteId
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartRepositoryInterface $cartRepository,
        CustomerCartResolver $customerCartResolver = null,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId = null
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartRepository = $cartRepository;
        $this->customerCartResolver = $customerCartResolver
            ?: ObjectManager::getInstance()->get(CustomerCartResolver::class);
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId
            ?: ObjectManager::getInstance()->get(QuoteIdToMaskedQuoteIdInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['source_cart_id'])) {
            throw new GraphQlInputException(__(
                'Required parameter "source_cart_id" is missing'
            ));
        }

        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__(
                'The current customer isn\'t authorized.'
            ));
        }
        $currentUserId = $context->getUserId();

        if (!isset($args['destination_cart_id'])) {
            try {
                $cart = $this->customerCartResolver->resolve($currentUserId);
            } catch (CouldNotSaveException $exception) {
                throw new GraphQlNoSuchEntityException(
                    __('Could not create empty cart for customer'),
                    $exception
                );
            }
            $customerMaskedCartId = $this->quoteIdToMaskedQuoteId->execute(
                (int) $cart->getId()
            );
        } else {
            if (empty($args['destination_cart_id'])) {
                throw new GraphQlInputException(__(
                    'The parameter "destination_cart_id" cannot be empty'
                ));
            }
        }

        $guestMaskedCartId = $args['source_cart_id'];
        $customerMaskedCartId = $customerMaskedCartId ?? $args['destination_cart_id'];

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        // passing customerId as null enforces source cart should always be a guestcart
        $guestCart = $this->getCartForUser->execute(
            $guestMaskedCartId,
            null,
            $storeId
        );
        $customerCart = $this->getCartForUser->execute(
            $customerMaskedCartId,
            $currentUserId,
            $storeId
        );
        $customerCart->merge($guestCart);
        $guestCart->setIsActive(false);
        $this->cartRepository->save($customerCart);
        $this->cartRepository->save($guestCart);
        return [
            'model' => $customerCart,
        ];
    }
}
