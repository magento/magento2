<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep as LogInCustomerOnStorefront;
use Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep as LogOutCustomerOnStorefront;

/**
 * Assert that http is used all over the Storefront.
 */
class AssertHttpUsedOnFrontend extends AbstractConstraint
{
    /**
     * Unsecured protocol format.
     *
     * @var string
     */
    private $unsecuredProtocol = \Magento\Framework\HTTP\PhpEnvironment\Request::SCHEME_HTTP;

    /**
     * Browser interface.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Customer account.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Validations execution.
     *
     * @param BrowserInterface $browser
     * @param Customer $customer
     * @return void
     */
    public function processAssert(BrowserInterface $browser, Customer $customer)
    {
        $this->browser = $browser;
        $this->customer = $customer;
        $this->customer->persist();

        // Log in to Customer Account on Storefront to assert that http is used indeed.
        $this->objectManager->create(LogInCustomerOnStorefront::class, ['customer' => $this->customer])->run();
        $this->assertUsedProtocol($this->unsecuredProtocol);

        // Log out from Customer Account on Storefront to assert that JS is deployed validly as a part of statics.
        $this->objectManager->create(LogOutCustomerOnStorefront::class)->run();
        $this->assertUsedProtocol($this->unsecuredProtocol);
    }

    /**
     * Assert that specified protocol is used on current page.
     *
     * @param string $expectedProtocol
     * @return void
     */
    protected function assertUsedProtocol($expectedProtocol)
    {
        if (substr($expectedProtocol, -3) !== "://") {
            $expectedProtocol .= '://';
        }

        \PHPUnit_Framework_Assert::assertStringStartsWith(
            $expectedProtocol,
            $this->browser->getUrl(),
            "$expectedProtocol is not used."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Unsecured URLs are used for Storefront pages.';
    }
}
