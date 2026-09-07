<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteId;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;
use Magento\QuoteGraphQl\Model\ErrorMapper;

/**
 * @inheritdoc
 */
class RemoveItemFromCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private GetCartForUser $getCartForUser;

    /**
     * @var CartItemRepositoryInterface
     */
    private CartItemRepositoryInterface $cartItemRepository;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteId
     */
    private MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId;

    /**
     * @var ArgumentsProcessorInterface
     */
    private ArgumentsProcessorInterface $argsSelection;

    /**
     * @var ErrorMapper
     */
    private ErrorMapper $errorMapper;

    /**
     * @param GetCartForUser $getCartForUser
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId
     * @param ArgumentsProcessorInterface $argsSelection
     * @param ErrorMapper $errorMapper
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        CartItemRepositoryInterface $cartItemRepository,
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId,
        ArgumentsProcessorInterface $argsSelection,
        ErrorMapper $errorMapper
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->cartItemRepository = $cartItemRepository;
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->argsSelection = $argsSelection;
        $this->errorMapper = $errorMapper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $processedArgs = $this->argsSelection->process($info->fieldName, $args);
        if (empty($processedArgs['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing.'));
        }
        $maskedCartId = $processedArgs['input']['cart_id'];
        try {
            $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart with ID "%masked_cart_id"', ['masked_cart_id' => $maskedCartId]),
                $exception,
                $this->errorMapper->getErrorMessageId('Could not find a cart with ID')
            );
        }

        if (empty($processedArgs['input']['cart_item_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_item_id" is missing.'));
        }
        $itemId = $processedArgs['input']['cart_item_id'];

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        /** Check if the current user is allowed to perform actions with the cart */
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        /*
         * Use Quote->removeItem() + CartRepository->save() instead of CartItemRepositoryInterface->deleteById()
         * to maintain consistency with REST API and other Magento implementations.
         *
         * This approach ensures:
         * - Cart state (is_virtual, totals) is properly recalculated
         * - Changes are persisted to database via explicit save
         * - Consistent behavior across all Magento APIs (GraphQL, REST, Controllers)
         *
         * Without explicit save, cart.is_virtual would remain stale when removing items,
         * causing checkout issues (e.g., requesting shipping address for virtual-only carts).
         */
        try {
            $cartItem = $cart->getItemById($itemId);
            if (!$cartItem) {
                throw new NoSuchEntityException(__('The cart doesn\'t contain the item'));
            }
            $cart->removeItem($itemId);
            $this->cartRepository->save($cart);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
