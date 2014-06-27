<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Test\Block\Form;

use Mtf\Block\Form;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Login
 * Form for frontend login
 */
class Login extends Form
{
    /**
     * Login button for registered customers
     *
     * @var string
     */
    protected $loginButton = '.action.login';

    /**
     * 'Register' customer button
     *
     * @var string
     */
    protected $registerButton = '.action.create';

    /**
     * Login customer in the Frontend
     *
     * @param FixtureInterface $customer
     *
     * @SuppressWarnings(PHPMD.ConstructorWithNameAsEnclosingClass)
     */
    public function login(FixtureInterface $customer)
    {
        $this->fill($customer);
        $this->submit();
        $this->waitForElementNotVisible($this->loginButton, Locator::SELECTOR_CSS);
    }

    /**
     * Submit login form
     */
    public function submit()
    {
        $this->_rootElement->find($this->loginButton, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Press 'Register' button
     */
    public function registerCustomer()
    {
        $this->_rootElement->find($this->registerButton, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Check whether block is visible
     *
     * @return bool
     */
    public function isVisible()
    {
        return $this->_rootElement->isVisible();
    }
}
