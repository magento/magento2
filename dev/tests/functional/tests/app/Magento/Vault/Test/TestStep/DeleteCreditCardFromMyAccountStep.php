<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\TestStep;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Payment\Test\Fixture\CreditCard;
use Magento\Vault\Test\Constraint\AssertStoredPaymentDeletedMessage;
use Magento\Vault\Test\Page\StoredPaymentMethods;

/**
 * Delete credit card from My Account step.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteCreditCardFromMyAccountStep implements TestStepInterface
{
    /**
     * Customer Fixture.
     *
     * @var Customer
     */
    private $customer;

    /**
     * Object manager.
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Customer account index page.
     *
     * @var CustomerAccountIndex
     */
    private $customerAccountIndex;

    /**
     * Credit card fixture.
     *
     * @var CreditCard
     */
    private $creditCard;

    /**
     * Assert message of success deletion of stored payment method.
     *
     * @var AssertStoredPaymentDeletedMessage
     */
    private $assertStoredPaymentDeletedMessage;

    /**
     * Stored payment methods page.
     *
     * @var StoredPaymentMethods
     */
    private $storedPaymentMethodsPage;

    /**
     * @param StoredPaymentMethods $storedPaymentMethodsPage
     * @param Customer $customer
     * @param ObjectManager $objectManager
     * @param CustomerAccountIndex $customerAccountIndex
     * @param AssertStoredPaymentDeletedMessage $assertStoredPaymentDeletedMessage
     * @param CreditCard $creditCard
     */
    public function __construct(
        StoredPaymentMethods $storedPaymentMethodsPage,
        Customer $customer,
        ObjectManager $objectManager,
        CustomerAccountIndex $customerAccountIndex,
        AssertStoredPaymentDeletedMessage $assertStoredPaymentDeletedMessage,
        CreditCard $creditCard
    ) {
        $this->storedPaymentMethodsPage = $storedPaymentMethodsPage;
        $this->customer = $customer;
        $this->objectManager = $objectManager;
        $this->customerAccountIndex = $customerAccountIndex;
        $this->assertStoredPaymentDeletedMessage = $assertStoredPaymentDeletedMessage;
        $this->creditCard = $creditCard;
    }

    /**
     * Run Delete credit card from My Account step.
     *
     * @return array
     */
    public function run()
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $this->customer]
        )->run();
        $this->customerAccountIndex->getAccountMenuBlock()->openMenuItem('Stored Payment Methods');
        $storedPaymentsBlock = $this->storedPaymentMethodsPage->getStoredPaymentsBlock();

        $creditCardData = $this->creditCard->getData();
        $creditCardNumber = preg_grep('/([a-z]+)_number/', array_flip($creditCardData));
        $lastFourDigits = substr(key($creditCardNumber), -4, 4);

        $availableCreditCards = $storedPaymentsBlock->getCreditCards();
        if (key_exists($lastFourDigits, $availableCreditCards)) {
            $storedPaymentsBlock->deleteCreditCard($availableCreditCards[$lastFourDigits]);
        }
        $this->assertStoredPaymentDeletedMessage->processAssert($this->storedPaymentMethodsPage);

        return ['deletedCreditCard' => $lastFourDigits];
    }
}
