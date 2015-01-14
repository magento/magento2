<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\Tax\Test\Fixture\TaxRule;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureFactory;

/**
 * Class AssertTaxRuleApplying
 * Abstract class for implementing assert applying
 */
abstract class AssertTaxRuleApplying extends AbstractConstraint
{
    /**
     * Initial tax rule
     *
     * @var TaxRule
     */
    protected $initialTaxRule;

    /**
     * Tax rule
     *
     * @var TaxRule
     */
    protected $taxRule;

    /**
     * Product simple
     *
     * @var CatalogProductSimple
     */
    protected $productSimple;

    /**
     * Checkout cart page
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Shipping carrier and method
     *
     * @var array
     */
    protected $shipping;

    /**
     * Tax Rule name
     *
     * @var string
     */
    protected $taxRuleCode;

    /**
     * Implementation assert
     *
     * @return void
     */
    abstract protected function assert();

    /**
     * 1. Creating product simple with custom tax product class
     * 2. Log In as customer
     * 3. Add product to shopping cart
     * 4. Estimate Shipping and Tax
     * 5. Implementation assert
     *
     * @param FixtureFactory $fixtureFactory
     * @param TaxRule $taxRule
     * @param CustomerAccountLogin $customerAccountLogin
     * @param CustomerAccountLogout $customerAccountLogout
     * @param CustomerInjectable $customer
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param AddressInjectable $address
     * @param array $shipping
     * @param Browser $browser
     * @param TaxRule $initialTaxRule
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function processAssert(
        FixtureFactory $fixtureFactory,
        TaxRule $taxRule,
        CustomerAccountLogin $customerAccountLogin,
        CustomerAccountLogout $customerAccountLogout,
        CustomerInjectable $customer,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        AddressInjectable $address,
        array $shipping,
        Browser $browser,
        TaxRule $initialTaxRule = null
    ) {
        $this->initialTaxRule = $initialTaxRule;
        $this->taxRule = $taxRule;
        $this->checkoutCart = $checkoutCart;
        $this->shipping = $shipping;

        if ($this->initialTaxRule !== null) {
            $this->taxRuleCode = ($this->taxRule->hasData('code'))
                ? $this->taxRule->getCode()
                : $this->initialTaxRule->getCode();
        } else {
            $this->taxRuleCode = $this->taxRule->getCode();
        }
        // Creating simple product with custom tax class
        /** @var \Magento\Tax\Test\Fixture\TaxClass $taxProductClass */
        $taxProductClass = $taxRule->getDataFieldConfig('tax_product_class')['source']->getFixture()[0];
        $this->productSimple = $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataSet' => '100_dollar_product_for_tax_rule',
                'data' => [
                    'tax_class_id' => ['tax_product_class' => $taxProductClass],
                ]
            ]
        );
        $this->productSimple->persist();
        // Customer login
        $customerAccountLogout->open();
        $customerAccountLogin->open();
        $customerAccountLogin->getLoginBlock()->login($customer);
        // Clearing shopping cart and adding product to shopping cart
        $checkoutCart->open()->getCartBlock()->clearShoppingCart();
        $browser->open($_ENV['app_frontend_url'] . $this->productSimple->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->clickAddToCart();
        // Estimate Shipping and Tax
        $checkoutCart->getShippingBlock()->openEstimateShippingAndTax();
        $checkoutCart->getShippingBlock()->fill($address);
        $checkoutCart->getShippingBlock()->clickGetQuote();
        $checkoutCart->getShippingBlock()->selectShippingMethod($shipping);
        $this->assert();
    }
}
