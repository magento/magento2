<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\AddProductsToCart;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Framework\Lock\LockManagerInterface;

/**
 * Add simple products to cart GraphQl resolver
 * {@inheritdoc}
 */
class AddSimpleProductsToCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var AddProductsToCart
     */
    private $addProductsToCart;

    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @param GetCartForUser $getCartForUser
     * @param AddProductsToCart $addProductsToCart
     * @param LockManagerInterface $lockManager
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        AddProductsToCart $addProductsToCart,
        LockManagerInterface $lockManager
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->addProductsToCart = $addProductsToCart;
        $this->lockManager = $lockManager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (empty($args['input']['cart_items'])
            || !is_array($args['input']['cart_items'])
        ) {
            throw new GraphQlInputException(__('Required parameter "cart_items" is missing'));
        }
        $cartItems = $args['input']['cart_items'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        $lockName = 'cart_processing_lock_' . $maskedCartId;
        while ($this->lockManager->isLocked($lockName)) {
            // wait till other process working with the same cart complete
            usleep(rand(100, 600));
        }
        $this->lockManager->lock($lockName, 1);

        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        $this->addProductsToCart->execute($cart, $cartItems);
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        $this->lockManager->unlock($lockName);
        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
}
