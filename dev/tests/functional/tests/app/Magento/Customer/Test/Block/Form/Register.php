<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Form;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class Register
 * Register new customer on Frontend
 */
class Register extends Form
{
    /**
     * 'Submit' form button
     *
     * @var string
     */
    protected $submit = '.action.submit';

    /**
     * Locator for customer attribute on New Order page
     *
     * @var string
     */
    protected $customerAttribute = "[name='%s']";

    /**
     * Locator for password error
     *
     * @var string
     */
    protected $passwordError = "#password-error";

    /**
     * Locator for password confirmation error
     *
     * @var string
     */
    protected $passwordConfirmationError = "#password-confirmation-error";

    /**
     * Create new customer account and fill billing address if it exists
     *
     * @param FixtureInterface $fixture
     * @param $address
     */
    public function registerCustomer(FixtureInterface $fixture, $address = null)
    {
        $this->fill($fixture);
        if ($address !== null) {
            $this->fill($address);
        }
        $this->_rootElement->find($this->submit, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Get password error on new customer registration form.
     *
     * @return string
     *
     */
    public function getPasswordError()
    {
        return $this->_rootElement->find($this->passwordError, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get password confirmation error on new customer registration form.
     *
     * @return string
     *
     */
    public function getPasswordConfirmationError()
    {
        return $this->_rootElement->find($this->passwordConfirmationError, Locator::SELECTOR_CSS)->getText();
    }
}
