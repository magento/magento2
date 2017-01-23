<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. All type products is created.
 *
 * Steps:
 * 1. Navigate to frontend.
 * 2. Open test product page.
 * 3. Add to cart test product.
 * 4. Perform all asserts.
 *
 * @group Shopping_Cart
 * @ZephyrId MAGETWO-63338, MAGETWO-63339, MAGETWO-63337
 */
class AddProductsToShoppingCartEntityPagerVerificationTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S2';
    /* end tags */

    /**
     * Browser interface.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Catalog product view page.
     *
     * @var CatalogProductView
     */
    private $catalogProductView;

    /**
     * Checkout cart page.
     *
     * @var CheckoutCart
     */
    protected $cartPage;

    /**
     * Config settings.
     *
     * @var string
     */
    private $configData;

    private $simpleProducts;

    /**
     * Prepare test data.
     *
     * @param BrowserInterface $browser
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $cartPage
     * @return void
     */
    public function __prepare(
        BrowserInterface $browser,
        FixtureFactory $fixtureFactory,
        CatalogProductView $catalogProductView,
        CheckoutCart $cartPage
    ) {
        $this->browser = $browser;
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogProductView = $catalogProductView;
        $this->cartPage = $cartPage;
        for ($i = 0; $i < 20; $i++) {
            $this->simpleProducts[$i] = $this->fixtureFactory
                ->createByCode(
                    'catalogProductSimple',
                    ['dataset' => 'default']
                );
            if ($this->simpleProducts[$i]->hasData('id') === false) {
                $this->simpleProducts[$i]->persist();
            }
        }
    }

    /**
     * Run test add products to shopping cart.
     *
     * @param array $cart
     * @param null $configData
     * @param int $itemsToRemove
     * @param array $productsData
     * @return array
     */
    public function test(array $cart, $configData = null, $itemsToRemove = 0, array $productsData = [])
    {
        // Preconditions
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $products = $this->prepareProducts($productsData);
        $products = array_merge_recursive($products, $this->simpleProducts);
        // Steps
        $this->addToCart($products);
        if ($itemsToRemove > 0) {
            $this->cartPage->open();
            $productsToRemove = array_slice($products, 1, $itemsToRemove);
            foreach ($productsToRemove as $product) {
                $this->cartPage->getCartBlock()->getCartItem($product)->removeItem();
            }
            $products = array_slice($products, $itemsToRemove + 1);
        }
        $cart['data']['items'] = ['products' => $products];

        return ['cart' => $this->fixtureFactory->createByCode('cart', $cart)];
    }

    /**
     * Create products.
     *
     * @param array $productList
     * @return array
     */
    protected function prepareProducts(array $productList)
    {
        $addToCartStep = ObjectManager::getInstance()->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $productList]
        );

        $result = $addToCartStep->run();
        return $result['products'];
    }

    /**
     * Add products to cart.
     *
     * @param array $products
     * @return void
     */
    protected function addToCart(array $products)
    {
        $addToCartStep = ObjectManager::getInstance()->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $products]
        );
        $addToCartStep->run();
    }

    /**
     * Reset config settings to default.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
