<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\ObjectManager;
use Magento\Multishipping\Test\Page\MultishippingCheckoutOverview;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Check that Terms and Conditions is present on the last checkout step - Order Review.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssertTermRequireMessageOnMultishippingCheckout extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Expected notification message
     */
    const NOTIFICATION_MESSAGE = 'This is a required field.';

    /**
     * Check that clicking "Place order" without setting checkbox for agreement will result in error message displayed
     * under condition.
     *
     * @param MultishippingCheckoutOverview $page
     * @param TestStepFactory $stepFactory
     * @param array $products
     * @param array $payment
     * @param array $shipping
     * @return void
     */
    public function processAssert(
        MultishippingCheckoutOverview $page,
        TestStepFactory $stepFactory,
        $products,
        $payment,
        $shipping
    ) {
        $customer = ['customer' => ['dataset' => 'johndoe_with_multiple_addresses']];
        $customer = $stepFactory->create(\Magento\Customer\Test\TestStep\CreateCustomerStep::class, $customer)->run();
        $products = $stepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
        )->run();
        $stepFactory->create(\Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class, $customer)->run();
        $stepFactory->create(\Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class, $products)->run();
        $stepFactory->create(\Magento\Multishipping\Test\TestStep\ProceedToMultipleAddressCheckoutStep::class)->run();
        $stepFactory->create(
            \Magento\Multishipping\Test\TestStep\FillCustomerAddressesStep::class,
            array_merge($products, $customer)
        )->run();
        $stepFactory->create(
            \Magento\Multishipping\Test\TestStep\FillShippingInformationStep::class,
            array_merge(['shippingMethod' => $shipping], $customer)
        )->run();
        $stepFactory->create(
            \Magento\Multishipping\Test\TestStep\SelectPaymentMethodStep::class,
            ['payment' => $payment]
        )->run();
        $stepFactory->create(
            \Magento\CheckoutAgreements\Test\TestStep\CheckTermOnMultishippingStep::class,
            ['agreementValue' => 'No']
        )->run();
        $stepFactory->create(\Magento\Multishipping\Test\TestStep\PlaceOrderStep::class)->run();
        \PHPUnit\Framework\Assert::assertEquals(
            self::NOTIFICATION_MESSAGE,
            $page->getAgreementReview()->getNotificationMassage(),
            'Notification required message of Terms and Conditions is absent.'
        );
        $stepFactory->create(
            \Magento\CheckoutAgreements\Test\TestStep\CheckTermOnMultishippingStep::class,
            ['agreementValue' => 'Yes']
        )->run();
        $stepFactory->create(\Magento\Multishipping\Test\TestStep\PlaceOrderStep::class)->run();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Validation error message for terms and conditions checkbox is present on multishipping checkout.';
    }
}
