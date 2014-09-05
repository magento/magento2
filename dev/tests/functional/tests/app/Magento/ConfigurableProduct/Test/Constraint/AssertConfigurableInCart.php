<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\ConfigurableProduct\Test\Fixture\CatalogProductConfigurable;

/**
 * Class AssertConfigurableInCart
 */
class AssertConfigurableInCart extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert configurable product, corresponds to the product in the cart
     *
     * @param CatalogProductView $catalogProductView
     * @param CatalogProductConfigurable $configurable
     * @param Browser $browser
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function processAssert(
        CatalogProductView $catalogProductView,
        CatalogProductConfigurable $configurable,
        Browser $browser,
        CheckoutCart $checkoutCart
    ) {
        //Add product to cart
        $browser->open($_ENV['app_frontend_url'] . $configurable->getUrlKey() . '.html');
        $configurableData = $configurable->getConfigurableAttributesData();
        if (!empty($configurableData)) {
            $configurableOption = $catalogProductView->getCustomOptionsBlock();
            foreach ($configurableData['attributes_data'] as $attribute) {
                $configurableOption->selectProductCustomOption($attribute['title']);
            }
        }
        $catalogProductView->getViewBlock()->clickAddToCart();

        $this->assertOnShoppingCart($configurable, $checkoutCart);
    }

    /**
     * Assert prices on the shopping Cart
     *
     * @param CatalogProductConfigurable $configurable
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    protected function assertOnShoppingCart(CatalogProductConfigurable $configurable, CheckoutCart $checkoutCart)
    {
        /** @var \Magento\ConfigurableProduct\Test\Fixture\CatalogProductConfigurable\Price $priceFixture */
        $priceFixture = $configurable->getDataFieldConfig('price')['source'];
        $pricePresetData = $priceFixture->getPreset();

        $price = $checkoutCart->getCartBlock()->getProductPriceByName($configurable->getName());
        \PHPUnit_Framework_Assert::assertEquals(
            $pricePresetData['cart_price'],
            $price,
            'Product price in shopping cart is not correct.'
        );
    }

    /**
     * Text of Visible in category assert
     *
     * @return string
     */
    public function toString()
    {
        return 'Product price in shopping cart is not correct.';
    }
}
