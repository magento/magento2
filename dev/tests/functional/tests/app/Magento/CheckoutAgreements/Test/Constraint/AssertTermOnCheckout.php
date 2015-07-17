<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Checkout\Test\Constraint\AssertOrderSuccessPlacedMessage;
use Magento\Checkout\Test\Page\CheckoutCart;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\ObjectManager;

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
     * @param FixtureFactory $fixtureFactory
     * @param ObjectManager $objectManager
     * @param string $product
     * @param BrowserInterface $browser
     * @param CatalogProductView $catalogProductView
     * @param CheckoutCart $checkoutCart
     * @param CheckoutOnepage $checkoutOnepage
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param AssertOrderSuccessPlacedMessage $assertOrderSuccessPlacedMessage
     * @param array $shipping
     * @param array $payment
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function processAssert(
        FixtureFactory $fixtureFactory,
        ObjectManager $objectManager,
        $product,
        BrowserInterface $browser,
        CatalogProductView $catalogProductView,
        CheckoutCart $checkoutCart,
        CheckoutOnepage $checkoutOnepage,
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        AssertOrderSuccessPlacedMessage $assertOrderSuccessPlacedMessage,
        $shipping,
        $payment
    ) {
        $createProductsStep = $objectManager->create(
            'Magento\Catalog\Test\TestStep\CreateProductsStep',
            ['products' => $product]
        );
        $product = $createProductsStep->run();

        $billingAddress = $fixtureFactory->createByCode('address', ['dataset' => 'default']);

        $browser->open($_ENV['app_frontend_url'] . $product['products'][0]->getUrlKey() . '.html');
        $catalogProductView->getViewBlock()->clickAddToCartButton();
        $catalogProductView->getMessagesBlock()->waitSuccessMessage();
        $checkoutCart->open();
        $checkoutCart->getCartBlock()->getOnepageLinkBlock()->proceedToCheckout();
        $checkoutOnepage->getLoginBlock()->clickContinue();
        $checkoutOnepage->getBillingBlock()->fill($billingAddress);
        $checkoutOnepage->getBillingBlock()->clickContinue();
        $checkoutOnepage->getShippingMethodBlock()->selectShippingMethod($shipping);
        $checkoutOnepage->getShippingMethodBlock()->clickContinue();
        $checkoutOnepage->getPaymentBlock()->selectPaymentMethod($payment);
        $checkoutOnepage->getPaymentBlock()->getSelectedPaymentMethodBlock()->clickPlaceOrder();

        \PHPUnit_Framework_Assert::assertEquals(
            self::NOTIFICATION_MESSAGE,
            $checkoutOnepage->getAgreementReview()->getNotificationMassage(),
            'Notification required message of Terms and Conditions is absent.'
        );

        $checkoutOnepage->getAgreementReview()->setAgreement('Yes');
        $checkoutOnepage->getAgreementReview()->placeOrder();
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
