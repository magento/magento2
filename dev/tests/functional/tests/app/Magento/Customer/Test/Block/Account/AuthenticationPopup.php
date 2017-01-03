<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Block\Account;

use Magento\Mtf\Block\Form;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Authentication popup block.
 */
class AuthenticationPopup extends Form
{
    /**
     * Login button.
     *
     * @var string
     */
    private $login = '.action.action-login.secondary';

    /**
     * Selector for loading mask element.
     *
     * @var string
     */
    private $loadingMask = '.loading-mask';

    /**
     * 'Create an Account' button.
     *
     * @var string
     */
    private $createAccountButton = '.action.action-register.primary';

    /**
     * Selector for password field with autocomplete off.
     *
     * @var string
     */
    private $passwordFieldWithAutocompleteOff = 'input[name="password"][autocomplete="off"]';

    /**
     * Checks if password field autocomplete is off.
     *
     * @return bool
     */
    public function isPasswordAutocompleteOff()
    {
        return $this->_rootElement->find($this->passwordFieldWithAutocompleteOff)->isVisible();
    }

    /**
     * Click 'Create an Account' button.
     *
     * @return void
     */
    public function createAccount()
    {
        $this->_rootElement->find($this->createAccountButton)->click();
    }

    /**
     * Login customer on authentication popup.
     *
     * @param Customer $customer
     * @return void
     */
    public function loginCustomer(Customer $customer)
    {
        $this->fill($customer);
        $this->_rootElement->find($this->login)->click();
        $this->waitForElementNotVisible($this->loadingMask);
    }
}
