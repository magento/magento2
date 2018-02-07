<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestCase;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create product according to dataset.
 * 2. Go to frontend.
 * 3. Add product to cart.
 *
 * Steps:
 * 1. Click on mini shopping cart icon.
 * 2. Click Edit.
 * 3. Fill data from dataset.
 * 4. Click Update.
 * 5. Perform all assertions.
 *
 * @group Mini_Shopping_Cart_(CS)
 * @ZephyrId MAGETWO-29812
 */
class UpdateProductFromMiniShoppingCartEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * Catalog product view page.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject data.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogProductView $catalogProductView
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CatalogProductView $catalogProductView,
        FixtureFactory $fixtureFactory
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->catalogProductView = $catalogProductView;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Update product from mini shopping cart.
     *
     * @param string $originalProduct
     * @param array $checkoutData
     * @return array
     */
    public function test($originalProduct, $checkoutData)
    {
        // Preconditions:
        $product = $this->createProduct($originalProduct);
        $this->addToCart($product);

        // Steps:
        $productData = $product->getData();
        $productData['checkout_data'] = $checkoutData;
        $newProduct = $this->createProduct(explode('::', $originalProduct)[0], [$productData]);
        $miniShoppingCart = $this->cmsIndex->getCartSidebarBlock();
        $miniShoppingCart->openMiniCart();
        $miniShoppingCart->getCartItem($newProduct)->clickEditItem();
        $this->catalogProductView->getViewBlock()->addToCart($newProduct);

        // Prepare data for asserts:
        $cart['data']['items'] = ['products' => [$newProduct]];
        $deletedCart['data']['items'] = ['products' => [$product]];

        return [
            'deletedCart' => $this->fixtureFactory->createByCode('cart', $deletedCart),
            'cart' => $this->fixtureFactory->createByCode('cart', $cart)
        ];
    }

    /**
     * Create product.
     *
     * @param string $product
     * @param array $data [optional]
     * @return FixtureInterface
     */
    protected function createProduct($product, array $data = [])
    {
        $createProductsStep = $this->objectManager->create(
            'Magento\Catalog\Test\TestStep\CreateProductsStep',
            ['products' => $product, 'data' => $data]
        );
        return $createProductsStep->run()['products'][0];
    }

    /**
     * Add product to cart.
     *
     * @param FixtureInterface $product
     * @return void
     */
    protected function addToCart(FixtureInterface $product)
    {
        $addToCartStep = $this->objectManager->create(
            'Magento\Checkout\Test\TestStep\AddProductsToTheCartStep',
            ['products' => [$product]]
        );
        $addToCartStep->run();
    }
}
