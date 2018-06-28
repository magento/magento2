<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Swatches\Test\TestStep\AddProductToCartFromCatalogCategoryPageStep as AddToCart;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Client\BrowserInterface;
use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Magento\Swatches\Test\Page\Adminhtml\CatalogProductSwatchAttributeEdit;
use Magento\Checkout\Test\Constraint\AssertCartItemsOptions;
use Magento\Swatches\Test\Constraint\AssertSelectedSwatchOptionsOnProductPage;

/**
 * @group Configurable_Product
 * @ZephyrId MAGETWO-82989
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateConfigurableProductWithSwatchFromShoppingCartTest extends Injectable
{
    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $testStep;

    /**
     * Factory for Test Fixtures.
     *
     * @var FixtureFactory
     */
    private $fixture;

    /**
     * Browser interface.
     *
     * @var BrowserInterface
     */
    private $browser;

    /**
     * "Cache Management" Admin panel page.
     *
     * @var AdminCache
     */
    protected $cachePage;

    /**
     * "Category" Storefront page.
     *
     * @var CatalogCategoryView
     */
    private $categoryPage;

    /**
     * "Product" Storefront page.
     *
     * @var CatalogProductView
     */
    private $productPage;

    /**
     * "Shopping Cart" Storefront page.
     *
     * @var CheckoutCart
     */
    private $cartPage;

    /**
     * Injection data.
     *
     * @param TestStepFactory $testStepFactory
     * @param FixtureFactory $fixtureFactory
     * @param BrowserInterface $browser
     * @param AdminCache $adminCache
     * @param CatalogCategoryView $categoryPage
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function __inject(
        TestStepFactory $testStepFactory,
        FixtureFactory $fixtureFactory,
        BrowserInterface $browser,
        AdminCache $adminCache,
        CatalogCategoryView $categoryPage,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart
    ) {
        $this->testStep = $testStepFactory;
        $this->fixture = $fixtureFactory;
        $this->browser = $browser;
        $this->cachePage = $adminCache;
        $this->categoryPage = $categoryPage;
        $this->productPage = $catalogProductView;
        $this->cartPage = $checkoutCart;
    }

    /**
     * Run the Test.
     *
     * @param ConfigurableProduct $product
     * @param array $colors
     * @param array $attributesToChange
     * @param AssertCartItemsOptions $assertCartItemsOptions
     * @param AssertSelectedSwatchOptionsOnProductPage $assertSelectedSwatchOptionsOnProductPage
     * @param CatalogProductAttributeIndex $attributeIndex
     * @param CatalogProductSwatchAttributeEdit $attribute
     * @return array
     */
    public function test(
        ConfigurableProduct $product,
        array $colors,
        array $attributesToChange,
        AssertCartItemsOptions $assertCartItemsOptions,
        AssertSelectedSwatchOptionsOnProductPage $assertSelectedSwatchOptionsOnProductPage,
        CatalogProductAttributeIndex $attributeIndex,
        CatalogProductSwatchAttributeEdit $attribute
    ) {
        // Preconditions go on (workaround until MAGETWO-83522 is fixed & backported)
        /** @var ConfigurableProduct $product */
        $product->persist();
        $this->updateSwatchAttributes($product, $attributeIndex, $attribute, $colors);

        $this->flushCache();
        $this->cartPage->open()->getCartItemBlock()->clearShoppingCart();

        // Steps
        $cart = $this->testStep->create(AddToCart::class, ['product' => $product])->run()['cart'];
        $assertCartItemsOptions->processAssert($this->cartPage, $cart);

        $this->cartPage->getCartItemBlock()->edit();
        $assertSelectedSwatchOptionsOnProductPage->processAssert($this->browser, $this->productPage, $product);

        $productToChange = $product->getData();
        $productToChange['checkout_data'] = $attributesToChange;
        $productToChange['price'] = $attributesToChange['cartItem']['price'];
        $product = $this->fixture->createByCode('configurableProduct', ['data' => $productToChange]);

        $this->productPage->getProductViewWithSwatchesBlock()->fillData($product);
        $this->productPage->getViewBlock()->clickUpdateCart();
        $this->cartPage->getMessagesBlock()->waitSuccessMessage();

        $cart = $this->fixture->createByCode(
            'cart',
            [
                'data' => [
                    'items' => [
                        'products' => [$product]
                    ]
                ]
            ]
        );
        return [
            'cart' => $cart
        ];
    }

    /**
     * Update (already used in Product) Swatch Attributes' Options with colors.
     *
     * @param ConfigurableProduct $productBeforeUpdate
     * @param CatalogProductAttributeIndex $attributeIndex
     * @param CatalogProductSwatchAttributeEdit $attribute
     * @param array $colors
     * @return void
     */
    private function updateSwatchAttributes($productBeforeUpdate, $attributeIndex, $attribute, $colors)
    {
        foreach ($productBeforeUpdate->getConfigurableAttributesData()['attributes_data'] as $attributeData) {
            $attributeIndex->open();
            $filter = ['attribute_code' => $attributeData['attribute_code']];
            $attributeIndex->getGrid()->searchAndOpen($filter);
            foreach ($colors as $optionKey => $color) {
                $attribute->getVisualSwatches()->applyOptionColor($optionKey, $color);
                $attribute->getPageActions()->saveAndContinue();
            }
        }
    }

    /**
     * Flush Magento Cache in Admin panel.
     *
     * @return void
     */
    private function flushCache()
    {
        $this->cachePage->open();
        $this->cachePage->getActionsBlock()->flushMagentoCache();
        $this->cachePage->getMessagesBlock()->waitSuccessMessage();
    }
}
