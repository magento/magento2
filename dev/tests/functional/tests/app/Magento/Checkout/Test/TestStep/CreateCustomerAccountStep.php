<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Create customer account on checkout one page success after place order.
 */
class CreateCustomerAccountStep implements TestStepInterface
{
    /**
     * "Success One Page Checkout" Storefront page.
     *
     * @var CheckoutOnepageSuccess
     */
    protected $checkoutOnepageSuccess;

    /**
     * Checkout method.
     *
     * @var string
     */
    private $checkoutMethod;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * "Create New Customer Account" Storefront page.
     *
     * @var CustomerAccountCreate
     */
<<<<<<< HEAD
    protected $customerAccountCreate;
=======
    private $customerAccountCreate;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

    /**
     * Customer specifies this password while registration.
     *
     * @var string
     */
<<<<<<< HEAD
    protected $customerPassword;
=======
    private $customerPassword;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

    /**
     * @constructor
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param string $checkoutMethod
     * @param FixtureFactory $fixtureFactory
     * @param CustomerAccountCreate $customerAccountCreate
     * @param null|string $customerPassword
     */
    public function __construct(
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        $checkoutMethod,
        FixtureFactory $fixtureFactory,
        CustomerAccountCreate $customerAccountCreate,
        $customerPassword = null
    ) {
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
        $this->checkoutMethod = $checkoutMethod;
        $this->fixtureFactory = $fixtureFactory;
        $this->customerAccountCreate = $customerAccountCreate;
        $this->customerPassword = $customerPassword;
    }

    /**
     * Create customer account.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkoutMethod === 'register') {
            $this->checkoutOnepageSuccess->getRegistrationBlock()->createAccount();

            $customerFixture = $this->fixtureFactory->createByCode(
                'customer',
                [
                    'data' => [
                        'password' => $this->customerPassword,
                        'password_confirmation' => $this->customerPassword,
<<<<<<< HEAD
                    ]
=======
                    ],
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
                ]
            );
            $this->customerAccountCreate->getRegisterForm()->registerCustomer($customerFixture);
        }
    }
}
