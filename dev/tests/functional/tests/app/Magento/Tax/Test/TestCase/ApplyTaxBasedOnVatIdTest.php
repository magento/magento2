<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\TestCase;

use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Checkout\Test\Fixture\Cart;
use Magento\Config\Test\Fixture\ConfigData;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Sales\Test\Fixture\OrderInjectable;
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
 * @group VAT_ID
 * @ZephyrId MAGETWO-13436
 */
class ApplyTaxBasedOnVatIdTest extends AbstractApplyVatIdTest
{
    /* tags */
    const MVP = 'no';
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
        $this->customer = $order->getDataFieldConfig('customer_id')['source']->getCustomer();
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        // Prepare data
        $taxRule->persist();
        $this->prepareVatConfiguration($vatConfig);
        $this->prepareCustomer($customerGroup);

        $products = $order->getEntityId()['products'];
        $cart = $this->fixtureFactory->createByCode(
            'cart',
            ['data' => array_merge($cart->getData(), ['items' => ['products' => $products]])]
        );
        $address = $this->customer->getDataFieldConfig('address')['source']->getAddresses()[0];

        $order->persist();
        $this->updateCustomer($customerGroup);

        // Steps
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $this->customer]
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
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
            'products' => $products
        ];
    }

    /**
     * Persist vat configuration
     *
     * @param string $vatConfig
     * @return void
     */
    private function prepareVatConfiguration($vatConfig)
    {
        $groupConfig = [
            'customer/create_account/viv_domestic_group' => [
                'value' => $this->vatGroups['valid_domestic_group']->getCustomerGroupId()
            ],
            'customer/create_account/viv_intra_union_group' => [
                'value' => $this->vatGroups['valid_intra_union_group']->getCustomerGroupId()
            ],
            'customer/create_account/viv_invalid_group' => [
                'value' => $this->vatGroups['invalid_group']->getCustomerGroupId()
            ],
            'customer/create_account/viv_error_group' => [
                'value' => $this->vatGroups['error_group']->getCustomerGroupId()
            ]
        ];
        $vatConfig = $this->fixtureFactory->createByCode(
            'configData',
            ['data' => array_replace_recursive($vatConfig->getSection(), $groupConfig)]
        );
        $vatConfig->persist();
    }

    /**
     * Persist customer with valid customer group
     *
     * @param string $customerGroup
     * @return void
     */
    private function prepareCustomer($customerGroup)
    {
        $customerData = array_merge(
            $this->customer->getData(),
            ['group_id' => ['value' => $this->vatGroups[$customerGroup]->getCustomerGroupId()]],
            ['address' => ['addresses' => $this->customer->getDataFieldConfig('address')['source']->getAddresses()]],
            ['email' => 'JohnDoe%isolation%@example.com']
        );

        unset($customerData['id']);
        $this->customer = $this->fixtureFactory->createByCode('customer', ['data' => $customerData]);
        $this->customer->persist();
    }

    /**
     * Update customer with customer group code for assert
     *
     * @param string $customerGroup
     * @return void
     */
    private function updateCustomer($customerGroup)
    {
        $customerData = array_merge(
            $this->customer->getData(),
            ['group_id' => ['value' => $this->vatGroups[$customerGroup]->getCustomerGroupCode()]]
        );

        $this->customer = $this->fixtureFactory->createByCode('customer', ['data' => $customerData]);
    }

    /**
     * Delete tax rules and disable VAT configuration.
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->objectManager->create(\Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep::class)->run();
    }
}
