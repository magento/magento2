<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\QuoteGraphQl\Model\Cart\ExtractDataFromCart;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\QuoteGraphQl\Model\Cart\UpdateCartItems as UpdateCartItemsService;

class UpdateCartItems implements ResolverInterface
{
    /**
     * @var ExtractDataFromCart
     */
    private $extractDataFromCart;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var UpdateCartItemsService
     */
    private $updateCartItems;

    /**
     * @param ExtractDataFromCart $extractDataFromCart
     * @param ArrayManager $arrayManager
     * @param UpdateCartItemsService $updateCartItems
     */
    public function __construct(
        ExtractDataFromCart $extractDataFromCart,
        ArrayManager $arrayManager,
        UpdateCartItemsService $updateCartItems
    ) {
        $this->extractDataFromCart = $extractDataFromCart;
        $this->arrayManager = $arrayManager;
        $this->updateCartItems = $updateCartItems;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $cartItems = $this->arrayManager->get('input/cart_items', $args);
        $maskedCartId = $this->arrayManager->get('input/cart_id', $args);

        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        if (!$cartItems) {
            throw new GraphQlInputException(__('Required parameter "cart_items  " is missing'));
        }

        try {
            $cart = $this->updateCartItems->update($maskedCartId, $cartItems);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }

        $cartData = $this->extractDataFromCart->execute($cart);

        return ['cart' => array_merge(['cart_id' => $maskedCartId], $cartData)];
    }
}
