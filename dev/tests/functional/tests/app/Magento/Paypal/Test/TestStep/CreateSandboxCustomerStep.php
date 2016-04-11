<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Paypal\Test\Fixture\SandboxCustomer;
use Magento\Paypal\Test\Page\Sandbox\AccountSignup;
use Magento\Paypal\Test\Page\Sandbox\SignupAddCard;
use Magento\Paypal\Test\Page\Sandbox\SignupCreate;

/**
 * Create PayPal Sandbox Customer.
 */
class CreateSandboxCustomerStep implements TestStepInterface
{
    /**
     * PayPal Sandbox customer fixture.
     *
     * @var SandboxCustomer
     */
    protected $sandboxCustomer;

    /**
     * PayPal Sandbox account signup page.
     *
     * @var AccountSignup
     */
    protected $accountSignup;

    /**
     * PayPal Sandbox account add credit card information page.
     *
     * @var SignupAddCard
     */
    protected $signupAddCard;

    /**
     * PayPal Sandbox account create page.
     *
     * @var SignupCreate
     */
    protected $signupCreate;

    /**
     * @constructor
     * @param SandboxCustomer $sandboxCustomer
     * @param AccountSignup $accountSignup
     * @param SignupAddCard $signupAddCard
     * @param SignupCreate $signupCreate
     */
    public function __construct(
        SandboxCustomer $sandboxCustomer,
        AccountSignup $accountSignup,
        SignupAddCard $signupAddCard,
        SignupCreate $signupCreate
    ) {
        $this->sandboxCustomer = $sandboxCustomer;
        $this->accountSignup = $accountSignup;
        $this->signupAddCard = $signupAddCard;
        $this->signupCreate = $signupCreate;
    }

    /**
     * Create new PayPal Sandbox Customer.
     *
     * @return void
     */
    public function run()
    {
        $this->accountSignup->open();
        $this->accountSignup->getSignupChooseAccountTypeBlock()->selectPersonalAccount();
        $this->accountSignup->getSignupPersonalAccountBlock()->fill($this->sandboxCustomer);
        $this->accountSignup->getSignupPersonalAccountBlock()->continueSignup();
        $this->signupCreate->getSignupCreateBlock()->fill($this->sandboxCustomer);
        $this->signupCreate->getSignupCreateBlock()->createAccount();
        $this->signupAddCard->getSignupAddCardBlock()->fill($this->sandboxCustomer);
        $this->signupAddCard->getSignupAddCardBlock()->linkCardToAccount();
    }
}
