<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Constraint;

use Magento\Checkout\Test\Constraint\AssertOrderSuccessPlacedMessage;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\ObjectManager;
use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;

/**
 * Class AssertTermOnCheckout
 * Check that Terms and Conditions is present on the last checkout step - Order Review.
 */
class AssertTermOnCheckout extends AbstractConstraint
{
    /**
     * Notification message
     */
    const NOTIFICATION_MESSAGE = 'This is a required field.';

    /**
     * Check that checkbox is present on the last checkout step - Order Review.
     * Check that after Place order without click on checkbox "Terms and Conditions" order was not successfully placed.
     * Check that after clicking on "Terms and Conditions" checkbox and "Place Order" button success place order message
     * appears.
     *
     * @param ObjectManager $objectManager
     * @param string $products
     * @param CheckoutOnepage $checkoutOnepage
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param AssertOrderSuccessPlacedMessage $assertOrderSuccessPlacedMessage
     * @param array $shipping
     * @param array $payment
     * @param CheckoutAgreement $agreement
     * @return void
     */
    public function processAssert(
        ObjectManager $objectManager,
        $products,
        CheckoutOnepage $checkoutOnepage,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        AssertOrderSuccessPlacedMessage $assertOrderSuccessPlacedMessage,
        $shipping,
        $payment,
        CheckoutAgreement $agreement
    ) {
        $paymentBlock = $checkoutOnepage->getPaymentBlock();
        $shippingAddressData = ['shippingAddress' => ['dataSet' => 'US_address_1']];
        $productsData = ['products' => $products];
        $shippingMethodData = ['shipping' => $shipping];
        $paymentData = ['payment' => $payment];

        $products = $objectManager->create('Magento\Catalog\Test\TestStep\CreateProductsStep', $productsData)->run();
        $objectManager->create('Magento\Checkout\Test\TestStep\AddProductsToTheCartStep', $products)->run();
        $objectManager->create('Magento\Checkout\Test\TestStep\ProceedToCheckoutStep')->run();
        $objectManager->create('Magento\Checkout\Test\TestStep\FillShippingAddressStep', $shippingAddressData)->run();
        $objectManager->create('Magento\Checkout\Test\TestStep\FillShippingMethodStep', $shippingMethodData)->run();
        $objectManager->create('Magento\Checkout\Test\TestStep\SelectPaymentMethodStep', $paymentData)->run();

        $paymentBlock->getSelectedPaymentMethodBlock()->clickPlaceOrder();
        \PHPUnit_Framework_Assert::assertEquals(
            self::NOTIFICATION_MESSAGE,
            $checkoutOnepage->getAgreementReview()->getNotificationMassage(),
            'Notification required message of Terms and Conditions is absent.'
        );
        $checkoutOnepage->getAgreementReview()->setAgreement('Yes', $agreement);
        $paymentBlock->getSelectedPaymentMethodBlock()->clickPlaceOrder();
        $assertOrderSuccessPlacedMessage->processAssert($checkoutOnepageSuccess);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order was placed with checkout agreement successfully.';
    }
}
