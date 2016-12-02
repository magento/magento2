<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep as LogInCustomerOnStorefront;
use Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep as LogOutCustomerOnStorefront;

/**
 * Assert that http is used all over the Storefront.
 * It would be great to assert somehow that browser console does not contain JS-related errors as well.
 */
class AssertHttpUsedOnFrontend extends AbstractConstraint
{
    /**
     * Unsecured protocol format.
     *
     * @var string
     */
    private $unsecuredProtocol = 'http://';

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
     * Prepare data for further validations execution.
     *
     * @param ObjectManager $objectManager
     * @param EventManagerInterface $eventManager
     * @param BrowserInterface $browser
     * @param Customer $customer
     * @param string $severity
     * @param bool $active
     */
    public function __construct(
        ObjectManager $objectManager,
        EventManagerInterface $eventManager,
        BrowserInterface $browser,
        Customer $customer,
        $severity = 'low',
        $active = true
    ) {
        parent::__construct($objectManager, $eventManager, $severity, $active);
        $this->browser = $browser;
        $this->customer = $customer;

        $this->customer->persist();
    }

    /**
     * Validations execution.
     *
     * @return void
     */
    public function processAssert()
    {
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
