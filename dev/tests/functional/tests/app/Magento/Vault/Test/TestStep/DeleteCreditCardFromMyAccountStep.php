<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Test\TestStep;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Vault\Test\Constraint\AssertCreditCardDeletedMessage;
use Magento\Vault\Test\Page\MyCreditCards;

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
     * @var AssertCreditCardDeletedMessage
     */
    private $assertCreditCardDeletedMessage;

    /**
     * @var $myCreditCardsPage
     */
    private $myCreditCardsPage;

    /**
     * DeleteCreditCardFromMyAccountStep constructor.
     *
     * @param MyCreditCards $myCreditCardsPage
     * @param Customer $customer
     * @param ObjectManager $objectManager
     * @param CustomerAccountIndex $customerAccountIndex
     * @param FixtureFactory $fixtureFactory
     * @param AssertCreditCardDeletedMessage $assertCreditCardDeletedMessage
     * @param InjectableFixture $creditCard
     * @param string $creditCardClass
     */
    public function __construct(
        MyCreditCards $myCreditCardsPage,
        Customer $customer,
        ObjectManager $objectManager,
        CustomerAccountIndex $customerAccountIndex,
        FixtureFactory $fixtureFactory,
        AssertCreditCardDeletedMessage $assertCreditCardDeletedMessage,
        InjectableFixture $creditCard,
        $creditCardClass = 'credit_card'
    )
    {
        $this->myCreditCardsPage = $myCreditCardsPage;
        $this->customer = $customer;
        $this->objectManager = $objectManager;
        $this->customerAccountIndex = $customerAccountIndex;
        $this->fixtureFactory = $fixtureFactory;
        $this->assertCreditCardDeletedMessage = $assertCreditCardDeletedMessage;
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
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $this->customer]
        )->run();
        $this->customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Credit Cards');
        $myCreditCardsBlock = $this->myCreditCardsPage->getCreditCardsBlock();

        $lastFourDigits = substr($this->creditCard->getData('credit_card_number'), -4, 4);

        $availableCreditCards = $myCreditCardsBlock->getCreditCards();
        if (key_exists($lastFourDigits, $availableCreditCards)) {
            $myCreditCardsBlock->deleteCreditCard($availableCreditCards[$lastFourDigits]);
        }
        $this->assertCreditCardDeletedMessage->processAssert($this->myCreditCardsPage);

        return ['deletedCreditCard' => $this->creditCard];
    }
}
