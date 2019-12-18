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
use Magento\Catalog\Api\ProductRepositoryInterface;

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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * AddSimpleProductsToCart constructor.
     *
     * @param GetCartForUser $getCartForUser
     * @param AddProductsToCart $addProductsToCart
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        AddProductsToCart $addProductsToCart,
        ProductRepositoryInterface $productRepository
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->addProductsToCart = $addProductsToCart;
        $this->productRepository = $productRepository;
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

        foreach ($cartItems as $cartItem) {
            $this->validationItemTypes($cartItem);
        }

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        $this->addProductsToCart->execute($cart, $cartItems);

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }

    /**
     * Validate Items Types
     *
     * @param $cartItem
     * @throws GraphQlInputException
     */
    protected function validationItemTypes($cartItem)
    {
        $values = $errorTypes = [];

        foreach ($cartItem['bundle_options'] as $bundleOption) {
            $values[$bundleOption['id']] = $bundleOption['value'];
        }

        $product = $this->productRepository->get($cartItem['data']['sku'], false, null, true);
        $optionsCollection = $product->getTypeInstance(true)->getOptionsCollection($product);

        foreach ($optionsCollection as $options) {
            $type = $options->getType();
            $optionId = $options->getOptionId();

            if (($type == 'radio' || $type == 'select') &&
                isset($values[$optionId]) &&
                count($values[$optionId]) > 1
            ) {
                $errorTypes[] = $type;
            }
        }

        if (!empty($errorTypes)) {
            throw new GraphQlInputException(
                __(
                    'Option type (%types) should have only one element.',
                    ['types' => implode(", ", $errorTypes)]
                )
            );
        }
    }
}
