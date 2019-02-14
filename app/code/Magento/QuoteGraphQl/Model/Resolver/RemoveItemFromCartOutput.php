<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\GuestCartItemRepositoryInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\QuoteGraphQl\Model\Cart\ExtractDataFromCart;

/**
 * @inheritdoc
 */
class RemoveItemFromCartOutput implements ResolverInterface
{
    /**
     * @var GuestCartItemRepositoryInterface
     */
    private $guestCartItemRepository;

    /**
     * @var GuestCartRepositoryInterface
     */
    private $guestCartRepository;

    /**
     * @var ExtractDataFromCart
     */
    private $extractDataFromCart;

    public function __construct(
        GuestCartItemRepositoryInterface $guestCartItemRepository,
        GuestCartRepositoryInterface $guestCartRepository,
        ExtractDataFromCart $extractDataFromCart
    ) {
        $this->guestCartItemRepository = $guestCartItemRepository;
        $this->guestCartRepository = $guestCartRepository;
        $this->extractDataFromCart = $extractDataFromCart;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        if (!isset($args['input']['cart_item_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_item_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];
        $itemId = $args['input']['cart_item_id'];

        try {
            $this->guestCartItemRepository->deleteById($maskedCartId, $itemId);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }

        $cart = $this->guestCartRepository->get($maskedCartId);

        $cartData = $this->extractDataFromCart->execute($cart);

        return ['cart' => array_merge(['cart_id' => $maskedCartId], $cartData)];
    }
}
