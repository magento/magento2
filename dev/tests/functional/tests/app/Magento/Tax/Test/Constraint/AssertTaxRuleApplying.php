<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Abstract class for implementing assert applying.
 */
abstract class AssertTaxRuleApplying extends AbstractConstraint
{
    /**
     * Initial tax rule.
     *
     * @var TaxRule
     */
    protected $initialTaxRule;

    /**
     * Tax rule.
     *
     * @var TaxRule
     */
    protected $taxRule;

    /**
     * Product simple.
     *
     * @var CatalogProductSimple
     */
    protected $productSimple;

    /**
     * Checkout cart page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Shipping carrier and method.
     *
     * @var array
     */
    protected $shipping;

    /**
     * Tax Rule name.
     *
     * @var string
     */
    protected $taxRuleCode;

    /**
     * Implementation assert.
     *
     * @return void
     */
    abstract protected function assert();

    /**
     * 1. Creating product simple with custom tax product class.
     * 2. Log In as customer.
     * 3. Add product to shopping cart.
     * 4. Estimate Shipping and Tax.
     * 5. Implementation assert.
     *
     * @param FixtureFactory $fixtureFactory
     * @param TaxRule $taxRule
     * @param Customer $customer
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param Address $address
     * @param array $shipping
     * @param BrowserInterface $browser
     * @param TaxRule $initialTaxRule
     * @return void
     */
    public function processAssert(
        FixtureFactory $fixtureFactory,
        TaxRule $taxRule,
        Customer $customer,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        Address $address,
        array $shipping,
        BrowserInterface $browser,
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
                'dataset' => 'product_100_dollar_for_tax_rule',
                'data' => [
                    'tax_class_id' => ['tax_product_class' => $taxProductClass],
                ]
            ]
        );
        $this->productSimple->persist();
        // Customer login
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
        // Clearing shopping cart and adding product to shopping cart
        $checkoutCart->open()->getCartBlock()->clearShoppingCart();
        $browser->open($_ENV['app_frontend_url'] . $this->productSimple->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->clickAddToCart();
        $catalogProductView->getMessagesBlock()->waitSuccessMessage();
        // Estimate Shipping and Tax
        $checkoutCart->open();
        $checkoutCart->getShippingBlock()->fillEstimateShippingAndTax($address);
        $checkoutCart->getShippingBlock()->selectShippingMethod($shipping);
        $this->assert();
    }
}
