<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountForgotPassword;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Precondition:
 * 1. Customer is created.
 *
 * Steps:
 * 1. Open forgot password page.
 * 2. Fill email.
 * 3. Click forgot password button.
 * 4. Check forgot password message.
 *
 * @group Customer
 * @ZephyrId MAGETWO-37145
 */
class ForgotPasswordOnFrontendTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Injection data.
     *
     * @param TestStepFactory $stepFactory
     * @return void
     */
    public function __inject(
        TestStepFactory $stepFactory
    ) {
        $this->stepFactory = $stepFactory;
    }

    /**
     * Create customer.
     *
     * @param Customer $customer
     * @param CustomerAccountForgotPassword $forgotPassword
     * @param null|string $configData
     * @return void
     */
    public function test(Customer $customer, CustomerAccountForgotPassword $forgotPassword, $configData)
    {
        $this->configData = $configData;

        // Preconditions
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        // Precondition
        $customer->persist();

        // Steps
        $forgotPassword->open();
        $forgotPassword->getForgotPasswordForm()->resetForgotPassword($customer);
    }
}
