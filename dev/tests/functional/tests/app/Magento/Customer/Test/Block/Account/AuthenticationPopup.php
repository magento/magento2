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
