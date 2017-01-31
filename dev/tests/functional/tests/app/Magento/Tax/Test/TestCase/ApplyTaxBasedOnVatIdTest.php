<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\TestCase;

use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Config\Test\Fixture\ConfigData;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Customer\Test\TestCase\AbstractApplyVatIdTest;

/**
 * Preconditions:
 * 1. Enable VAT functionality.
 * 2. Create tax rule.
 * 3. Configure tax displaying.
 * 4. Create simple product.
 * 5. Create customer.
 *
 * Steps:
 * 1. Place order with by created customer.
 * 2. Go to frontend as logged In Customer.
 * 3. Add simple product to Shopping Cart from product page.
 * 4. Go to Shopping Cart.
 * 5. In 'Estimate Shipping and Tax' section specify destination and click 'Get a Quote' button.
 * 6. Perform assertions.
 *
 * @group VAT_ID_(CS)
 * @ZephyrId MAGETWO-13436
 */
class ApplyTaxBasedOnVatIdTest extends AbstractApplyVatIdTest
{
    /* tags */
    const MVP = 'no';
    const DOMAIN = 'CS';
    const TEST_TYPE = '3rd_party_test';
    /* end tags */

    /**
     * Shopping cart page.
     *
     * @var CheckoutCart
     */
    protected $checkoutCart;

    /**
     * Inject page.
     *
     * @param CheckoutCart $checkoutCart
     * @return void
     */
    public function __inject(CheckoutCart $checkoutCart)
    {
        $this->checkoutCart = $checkoutCart;
    }

    /**
     * Automatic Apply Tax Based on VAT ID.
     *
     * @param ConfigData $vatConfig
     * @param OrderInjectable $order
     * @param TaxRule $taxRule
     * @param Cart $cart
     * @param string $configData
     * @param string $customerGroup
     * @return array
     */
    public function test(
        ConfigData $vatConfig,
        OrderInjectable $order,
        TaxRule $taxRule,
        Cart $cart,
        $configData,
        $customerGroup
    ) {
        // Preconditions
        $this->configData = $configData;
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData]
        )->run();
        $taxRule->persist();
        // Prepare data
        $this->customer = $order->getDataFieldConfig('customer_id')['source']->getCustomer();
        $address = $this->customer->getDataFieldConfig('address')['source']->getAddresses()[0];
        $this->prepareVatConfig($vatConfig, $customerGroup);
        $poducts = $order->getEntityId()['products'];
        $cart = $this->fixtureFactory->createByCode(
            'cart',
            ['data' => array_merge($cart->getData(), ['items' => ['products' => $poducts]])]
        );

        // Steps
        $order->persist();
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $this->customer]
        )->run();
        $this->objectManager->create(
            'Magento\Checkout\Test\TestStep\AddProductsToTheCartStep',
            $order->getEntityId()
        )->run();
        $this->checkoutCart->open();
        $this->checkoutCart->getCartBlock()->waitCartContainerLoading();
        $this->checkoutCart->getShippingBlock()->fillEstimateShippingAndTax($address);
        $this->checkoutCart->getCartBlock()->waitCartContainerLoading();

        return [
            'customer' => $this->customer,
            'address' => $address,
            'orderId' => $order->getId(),
            'cart' => $cart,
            'products' => $poducts
        ];
    }

    /**
     * Delete tax rules and disable VAT configuration.
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->objectManager->create('Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep')->run();
    }
}
