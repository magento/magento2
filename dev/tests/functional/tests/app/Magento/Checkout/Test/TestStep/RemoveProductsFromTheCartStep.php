<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Checkout\Test\Page\CheckoutCart;

/**
 * Remove products from the cart.
 */
class RemoveProductsFromTheCartStep implements TestStepInterface
{
    /**
     * Array with products.
     *
     * @var array
     */
    private $products;

    /**
     * Page of checkout page.
     *
     * @var CheckoutCart
     */
    private $cartPage;

    /**
     * Quantity of items that should be removed from shoping cart.
     *
     * @var int|null
     */
    private $itemsToRemove;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @param CheckoutCart $cartPage
     * @param FixtureFactory $fixtureFactory
     * @param array $products
     * @param int|null $itemsToRemove
     */
    public function __construct(
        CheckoutCart $cartPage,
        FixtureFactory $fixtureFactory,
        array $products,
        $itemsToRemove = null
    ) {
        $this->cartPage = $cartPage;
        $this->fixtureFactory = $fixtureFactory;
        $this->products = $products;
        $this->itemsToRemove = $itemsToRemove;
    }

    /**
     * Remove products from the shopping cart.
     *
     * @return array
     */
    public function run()
    {
        if ($this->itemsToRemove !== null) {
            $this->cartPage->open();
            $productsToRemove = array_slice($this->products, 1, $this->itemsToRemove);
            foreach ($productsToRemove as $product) {
                $this->cartPage->getCartBlock()->getCartItem($product)->removeItem();
            }
            $this->products = array_slice($this->products, $this->itemsToRemove + 1);
        }
        $cart['data']['items'] = ['products' => $this->products];

        return ['cart' => $this->fixtureFactory->createByCode('cart', $cart)];
    }
}
