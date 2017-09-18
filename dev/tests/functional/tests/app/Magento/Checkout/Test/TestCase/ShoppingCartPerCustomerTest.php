<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Util\Command\Cli\Cache;

/**
 * Precondition:
 * 1. Flush cache.
 * 2. Create products.
 * 3. Create 2 customers with unique names.
 * 4. Add products with different options to the shopping cart for each customer.
 *
 * Steps:
 * 1. Go to the storefront.
 * 2. Login as 1st customer.
 * 3. Open the shopping cart page twice.
 * 4. Perform all assertions.
 *
 * @group Shopping_Cart
 * @ZephyrId MAGETWO-37214
 */
class ShoppingCartPerCustomerTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Shopping cart page.
     *
     * @var CheckoutCart
     */
    private $checkoutCart;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Cli command to do operations with cache.
     *
     * @var Cache
     */
    private $cache;

    /**
     * Inject data.
     *
     * @param CheckoutCart $checkoutCart
     * @param FixtureFactory $fixtureFactory
     * @param Cache $cache
     * @return void
     */
    public function __inject(
        CheckoutCart $checkoutCart,
        FixtureFactory $fixtureFactory,
        Cache $cache
    ) {
        $this->checkoutCart = $checkoutCart;
        $this->fixtureFactory = $fixtureFactory;
        $this->cache = $cache;
    }

    /**
     * Run test for shopping cart with different customers.
     *
     * @param array $productsData
     * @param string $customerDataset
     * @param array $checkoutData
     * @return array
     */
    public function test(
        array $productsData,
        $customerDataset,
        array $checkoutData
    ) {
        //Preconditions
        $this->cache->flush();
        $products = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $productsData]
        )->run()['products'];

        $customers = [];
        $cartFixtures = [];
        for ($i = 0; $i < count($checkoutData); $i++) {
            $customers[$i] = $this->fixtureFactory->createByCode('customer', ['dataset' => $customerDataset]);
            $customers[$i]->persist();

            if (isset($checkoutData[$i])) {
                $cartFixtures[$i] = $this->prepareShoppingCart($customers[$i], $checkoutData[$i], $products);
            }
        }

        //Steps
        if (!empty($customers[0])) {
            $this->objectManager->create(
                \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
                ['customer' => $customers[0]]
            )->run();
            $this->checkoutCart->open();
            $this->checkoutCart->open();
        }

        return [
            'customers' => $customers,
            'cartFixtures' => $cartFixtures,
        ];
    }

    /**
     * Prepare shopping cart for customer.
     *
     * @param Customer $customer
     * @param array $checkoutData
     * @param array $products
     * @return \Magento\Checkout\Test\Fixture\Cart|null
     */
    private function prepareShoppingCart(Customer $customer, array $checkoutData, array $products)
    {
        $productsInCart = [];
        if (isset($checkoutData['items'])) {
            foreach ($checkoutData['items'] as $index => $dataset) {
                if (isset($products[$index])) {
                    $productFixture = $this->fixtureFactory->create(
                        get_class($products[$index]),
                        [
                            'data' => array_merge(
                                $products[$index]->getData(),
                                ['checkout_data' => ['dataset' => $dataset]]
                            )
                        ]
                    );
                    $productsInCart[] = $productFixture;
                }
            }
        }

        if (!empty($productsInCart)) {
            $this->objectManager->create(
                \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
                ['customer' => $customer]
            )->run();

            $this->objectManager->create(
                \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
                ['products' => $productsInCart]
            )->run();

            $cart['data'] = isset($checkoutData['totals']) ? $checkoutData['totals'] : [];
            $cart['data']['items'] = ['products' => $productsInCart];
            return $this->fixtureFactory->createByCode('cart', $cart);
        }

        return null;
    }
}
