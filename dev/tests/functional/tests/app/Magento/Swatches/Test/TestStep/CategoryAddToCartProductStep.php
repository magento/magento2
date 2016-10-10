<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Add configurable product to cart
 */
class CategoryAddToCartProductStep implements TestStepInterface
{
    /**
     * @var \Magento\Swatches\Test\Block\Product\ProductList\ProductItem
     */
    private $categoryProductBlock;

    /**
     * @var \Magento\Checkout\Test\Page\CheckoutCart
     */
    private $checkoutCartPage;

    /**
     * @var \Magento\Swatches\Test\Fixture\ConfigurableProduct
     */
    private $product;

    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * OpenProductInCatalog constructor.
     *
     * @param \Magento\Checkout\Test\Page\CheckoutCart $checkoutCart
     * @param \Magento\Swatches\Test\Block\Product\ProductList\ProductItem $categoryProductBlock
     * @param FixtureFactory $fixtureFactory
     * @param \Magento\Swatches\Test\Fixture\ConfigurableProduct $product
     */
    public function __construct(
        \Magento\Checkout\Test\Page\CheckoutCart $checkoutCart,
        \Magento\Swatches\Test\Block\Product\ProductList\ProductItem $categoryProductBlock,
        FixtureFactory $fixtureFactory,
        $product
    ) {
        $this->categoryProductBlock = $categoryProductBlock;
        $this->checkoutCartPage = $checkoutCart;
        $this->fixtureFactory = $fixtureFactory;
        $this->product = $product;
    }

    /**
     * Update configurable product.
     *
     * @return array
     */
    public function run()
    {
        $this->categoryProductBlock->clickAddToCart();
        $cart = [
            'data' => [
                'items' => [
                    'products' => [$this->product]
                ]
            ]
        ];
        return [
            'checkoutCart' => $this->checkoutCartPage,
            'cart' => $this->fixtureFactory->createByCode('cart', $cart)
        ];
    }
}
