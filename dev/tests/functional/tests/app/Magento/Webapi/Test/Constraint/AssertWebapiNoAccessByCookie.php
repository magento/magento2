<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Assert that customer should not have customer webapi access through cookies.
 *
 * @security-private
 */
class AssertWebapiNoAccessByCookie extends AbstractConstraint
{
    /**
     * 'Submit Request' button xpath selector.
     *
     * @var string
     */
    private $submitBtn = '//form/input[@value=\'Submit Request\']';

    /**
     * Perform assertions.
     *
     * @param Customer $customer
     * @param CmsPage $cms
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        Customer $customer,
        CmsPage $cms,
        BrowserInterface $browser
    ) {
        // Create and login a customer on frontend
        $customer->persist();
        /** @var \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep $customerLoginStep */
        $customerLoginStep = $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        );
        $customerLoginStep->run();

        // Go to cms page with form as logged in customer and submit request
        $browser->open($_ENV['app_frontend_url'] . $cms->getIdentifier());
        $browser->find($this->submitBtn, Locator::SELECTOR_XPATH)->click();

        \PHPUnit_Framework_Assert::assertContains(
            'Consumer is not authorized to access',
            $browser->getHtmlSource(),
            'Customer should not have customer webapi access through cookies.'
        );

        $customerLoginStep->cleanup();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer does not have customer webapi access through cookies.';
    }
}
