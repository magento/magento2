<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\TestCase;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;
use Magento\Vault\Test\Constraint\AssertCreditCardNotPresentOnCheckout;

/**
 * Preconditions:
 * 1. Credit card is saved during checkout
 *
 * Steps:
 * 1. Log in Storefront.
 * 2. Click 'My Account' link.
 * 3. Click 'My Credit Cards' tab.
 * 4. Click the 'Delete' button next to stored credit card.
 * 5. Click 'Delete' button.
 * 6. Go to One page Checkout
 * 7. Perform assertions.
 *
 * @group Vault_(CS)
 * @ZephyrId MAGETWO-54059, MAGETWO-54072, MAGETWO-54068, MAGETWO-54015, MAGETWO-54011
 */
class DeleteSavedCreditCardTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    const TEST_TYPE = '3rd_party_test';
    /* end tags */

    /**
     * Runs delete saved credit card test.
     *
     * @param AssertCreditCardNotPresentOnCheckout $assertCreditCardNotPresentOnCheckout
     * @param CheckoutOnepage $checkoutOnepage
     * @param $products
     * @param $configData
     * @param $customer
     * @param $checkoutMethod
     * @param $shippingAddress
     * @param $shipping
     * @param array $payments
     * @param $creditCardSave
     */
    public function test(
        AssertCreditCardNotPresentOnCheckout $assertCreditCardNotPresentOnCheckout,
        CheckoutOnepage $checkoutOnepage,
        $products,
        $configData,
        $customer,
        $checkoutMethod,
        $shippingAddress,
        $shipping,
        array $payments,
        $creditCardSave
    ) {
        // Preconditions
        $products = $this->prepareProducts($products);
        $this->setupConfiguration($configData);
        $customer = $this->createCustomer($customer);

        // Steps
        foreach ($payments as $key => $payment) {
            $this->addToCart($products);
            $this->proceedToCheckout();
            if($key < 1) { // if this is the first order to be placed
                $this->selectCheckoutMethod($checkoutMethod, $customer);
                $this->fillShippingAddress($shippingAddress);
            }
            $this->fillShippingMethod($shipping);
            if ($key >= 2) { // if this order will be placed via stored credit card
                $this->useSavedCreditCard($payment);
            } else {
                $this->selectPaymentMethod($payment, $payment['creditCardClass'], $payment['creditCard']);
                $this->saveCreditCard($payment, $creditCardSave);
            }
            $this->placeOrder();
        }
        // Delete credit cards from My Account and verify they are not available on checkout
        $paymentsCount = count($payments);
        for($i = 2; $i < $paymentsCount; $i++) {
            $deletedCard = $this->deleteCreditCardFromMyAccount(
                $customer,
                $payments[$i]['creditCard'],
                $payments[$i]['creditCardClass']
            );
            $this->addToCart($products);
            $this->proceedToCheckout();
            $this->fillShippingMethod($shipping);
            $assertCreditCardNotPresentOnCheckout->processAssert(
                $checkoutOnepage,
                $deletedCard['deletedCreditCard']
            );
        }
    }

    /**
     * @param $configData
     */
    protected function setupConfiguration($configData)
    {
        $setupConfigurationStep = ObjectManager::getInstance()->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        );

        $setupConfigurationStep->run();
    }

    /**
     * Create products
     *
     * @param string $productList
     * @return array
     */
    protected function prepareProducts($productList)
    {
        $addToCartStep = ObjectManager::getInstance()->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $productList]
        );

        $result = $addToCartStep->run();
        return $result['products'];
    }

    /**
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
     * @return void
     */
    protected function proceedToCheckout()
    {
        $clickProceedToCheckoutStep = ObjectManager::getInstance()->create(
            \Magento\Checkout\Test\TestStep\ProceedToCheckoutStep::class
        );
        $clickProceedToCheckoutStep->run();
    }

    /**
     * @param array $customer
     */
    protected function createCustomer(array $customer)
    {
        $createCustomerStep = ObjectManager::getInstance()->create(
            \Magento\Customer\Test\TestStep\CreateCustomerStep::class,
            ['customer' => $customer]
        );
        $result = $createCustomerStep->run();
        return $result['customer'];
    }

    /**
     * @param $checkoutMethod
     * @param $customer
     */
    protected function selectCheckoutMethod($checkoutMethod, $customer)
    {
        $selectCheckoutMethodStep = ObjectManager::getInstance()->create(
            \Magento\Checkout\Test\TestStep\SelectCheckoutMethodStep::class,
            [
                'checkoutMethod' => $checkoutMethod,
                'customer' => $customer,
            ]
        );
        $selectCheckoutMethodStep->run();
    }

    /**
     * @param array $shippingAddress
     */
    protected function fillShippingAddress(array $shippingAddress)
    {
        $fillShippingAddressStep = ObjectManager::getInstance()->create(
            \Magento\Checkout\Test\TestStep\FillShippingAddressStep::class,
            ['shippingAddress' => $shippingAddress]
        );
        $fillShippingAddressStep->run();
    }

    /**
     * Add products to cart
     *
     * @param array $shipping
     */
    protected function fillShippingMethod(array $shipping)
    {
        $fillShippingMethodStep = ObjectManager::getInstance()->create(
            \Magento\Checkout\Test\TestStep\FillShippingMethodStep::class,
            ['shipping' => $shipping]
        );
        $fillShippingMethodStep->run();
    }

    /**
     * @param array $payment
     * @param $creditCardClass
     * @param array $creditCard
     */
    protected function selectPaymentMethod(array $payment, $creditCardClass, array $creditCard)
    {
        $selectPaymentMethodStep = ObjectManager::getInstance()->create(
            \Magento\Checkout\Test\TestStep\SelectPaymentMethodStep::class,
            [
                'payment' => $payment,
                'creditCardClass' => $creditCardClass,
                'creditCard' => $creditCard,
            ]
        );
        $selectPaymentMethodStep->run();
    }

    /**
     * Add products to cart
     *
     * @param $payment
     * @param $creditCardSave
     */
    protected function saveCreditCard($payment, $creditCardSave)
    {
        $saveCreditCardStep = ObjectManager::getInstance()->create(
            \Magento\Vault\Test\TestStep\SaveCreditCardStep::class,
            [
                'creditCardSave' => $creditCardSave,
                'payment' => $payment
            ]
        );
        $saveCreditCardStep->run();
    }
    
    /**
     * @return void
     */
    protected function fillBillingInformation()
    {
        $fillBillingInformationStep = ObjectManager::getInstance()->create(
            \Magento\Checkout\Test\TestStep\FillBillingInformationStep::class
        );
        $fillBillingInformationStep->run();
    }
    
    /**
     * @return void
     */
    protected function placeOrder()
    {
        $placeOrderStep = ObjectManager::getInstance()->create(
            \Magento\Checkout\Test\TestStep\PlaceOrderStep::class
        );
        $placeOrderStep->run();
    }

    /**
     * @param $payment
     */
    protected function useSavedCreditCard($payment)
    {
        $useSavedCreditCardStep = ObjectManager::getInstance()->create(
            \Magento\Vault\Test\TestStep\UseSavedCreditCardStep::class,
            ['payment' => $payment]
        );
        $useSavedCreditCardStep->run();
    }

    /**
     * @param $customer
     * @param $creditCard
     * @param $creditCardClass
     */
    protected function deleteCreditCardFromMyAccount($customer, $creditCard, $creditCardClass)
    {
        $deleteCreditCardFromMyAccountStep = ObjectManager::getInstance()->create(
            \Magento\Vault\Test\TestStep\DeleteCreditCardFromMyAccountStep::class,
            [
                'customer' => $customer,
                'creditCard' => $creditCard,
                'creditCardClass' => $creditCardClass
            ]
        );
        $deletedCard = $deleteCreditCardFromMyAccountStep->run();
        return $deletedCard;
    }
}
