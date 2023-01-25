<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\QuoteGraphQl\Model\Cart\GetCartProducts;

/**
 * @inheritdoc
 */
class CartItems implements ResolverInterface
{
    /**
     * @var GetCartProducts
     */
    private $getCartProducts;

    /** @var Uid */
    private $uidEncoder;

    /**
     * @param GetCartProducts $getCartProducts
     * @param Uid $uidEncoder
     */
    public function __construct(
        GetCartProducts $getCartProducts,
        Uid $uidEncoder
    ) {
        $this->getCartProducts = $getCartProducts;
        $this->uidEncoder = $uidEncoder;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $cart = $value['model'];

        $itemsData = [];
        $cartProductsData = $this->getCartProductsData($cart);
        $cartItems = $cart->getAllVisibleItems();
        /** @var QuoteItem $cartItem */
        foreach ($cartItems as $cartItem) {
            $productId = $cartItem->getProduct()->getId();
            if (!isset($cartProductsData[$productId])) {
                $itemsData[] = new GraphQlNoSuchEntityException(
                    __("The product that was requested doesn't exist. Verify the product and try again.")
                );
                continue;
            }
            $productData = $cartProductsData[$productId];

            $itemsData[] = [
                'id' => $cartItem->getItemId(),
                'uid' => $this->uidEncoder->encode((string) $cartItem->getItemId()),
                'quantity' => $cartItem->getQty(),
                'product' => $productData,
                'model' => $cartItem,
            ];
        }
        return $itemsData;
    }

    /**
     * Get product data for cart items
     *
     * @param Quote $cart
     * @return array
     */
    private function getCartProductsData(Quote $cart): array
    {
        $products = $this->getCartProducts->execute($cart);
        $productsData = [];
        foreach ($products as $product) {
            $productsData[$product->getId()] = $product->getData();
            $productsData[$product->getId()]['model'] = $product;
            $productsData[$product->getId()]['uid'] = $this->uidEncoder->encode((string) $product->getId());
        }

        return $productsData;
    }
}
