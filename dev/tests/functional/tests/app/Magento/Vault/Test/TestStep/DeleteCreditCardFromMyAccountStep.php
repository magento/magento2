<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\TestStep;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Vault\Test\Constraint\AssertStoredPaymentDeletedMessage;
use Magento\Vault\Test\Page\StoredPaymentMethods;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteCreditCardFromMyAccountStep implements TestStepInterface
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CustomerAccountIndex
     */
    private $customerAccountIndex;

    /**
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * @var \Magento\Mtf\Fixture\FixtureInterface
     */
    private $creditCard;

    /**
     * @var AssertStoredPaymentDeletedMessage
     */
    private $assertStoredPaymentDeletedMessage;

    /**
     * @var StoredPaymentMethods
     */
    private $storedPaymentMethodsPage;

    /**
     * DeleteCreditCardFromMyAccountStep constructor.
     *
     * @param StoredPaymentMethods $storedPaymentMethodsPage
     * @param Customer $customer
     * @param ObjectManager $objectManager
     * @param CustomerAccountIndex $customerAccountIndex
     * @param FixtureFactory $fixtureFactory
     * @param AssertStoredPaymentDeletedMessage $assertStoredPaymentDeletedMessage
     * @param array $creditCard
     * @param string $creditCardClass
     */
    public function __construct(
        StoredPaymentMethods $storedPaymentMethodsPage,
        Customer $customer,
        ObjectManager $objectManager,
        CustomerAccountIndex $customerAccountIndex,
        FixtureFactory $fixtureFactory,
        AssertStoredPaymentDeletedMessage $assertStoredPaymentDeletedMessage,
        array $creditCard,
        $creditCardClass = 'credit_card'
    ) {
        $this->storedPaymentMethodsPage = $storedPaymentMethodsPage;
        $this->customer = $customer;
        $this->objectManager = $objectManager;
        $this->customerAccountIndex = $customerAccountIndex;
        $this->fixtureFactory = $fixtureFactory;
        $this->assertStoredPaymentDeletedMessage = $assertStoredPaymentDeletedMessage;
        $this->creditCard = $fixtureFactory->createByCode($creditCardClass, ['dataset' => $creditCard['dataset']]);
    }

    /**
     * @inheritdoc
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
