<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * One page checkout status login popup block.
 */
class LoginPopup extends Form
{
    /**
     * Login button
     *
     * @var string
     */
    protected $login = '[id="send2"]';

    /**
     * Selector for loading mask element
     *
     * @var string
     */
    protected $loadingMask = '.loading-mask';

    /**
     * Login customer during checkout.
     *
     * @param FixtureInterface $customer
     * @return void
     */
    public function loginCustomer(FixtureInterface $customer)
    {
        $this->fill($customer);
        $this->_rootElement->find($this->login)->click();
        $this->waitForElementNotVisible($this->loadingMask);
    }
}
