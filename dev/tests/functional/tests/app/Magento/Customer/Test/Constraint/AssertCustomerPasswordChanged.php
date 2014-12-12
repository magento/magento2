<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureFactory;

/**
 * Class AssertCustomerPasswordChanged
 * Check that login again to frontend with new password was success
 */
class AssertCustomerPasswordChanged extends AbstractConstraint
{
    /**
     * Welcome message after login
     */
    const SUCCESS_MESSAGE = 'Hello, %s!';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that login again to frontend with new password was success
     *
     * @param FixtureFactory $fixtureFactory
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountIndex $customerAccountIndex
     * @param CustomerInjectable $initialCustomer
     * @param CustomerInjectable $customer
     * @return void
     */
    public function processAssert(
        FixtureFactory $fixtureFactory,
        CmsIndex $cmsIndex,
        CustomerAccountIndex $customerAccountIndex,
        CustomerInjectable $initialCustomer,
        CustomerInjectable $customer
    ) {
        $cmsIndex->open();
        if ($cmsIndex->getLinksBlock()->isVisible()) {
            $cmsIndex->getLinksBlock()->openLink('Log Out');
        }

        $customer = $fixtureFactory->createByCode(
            'customerInjectable',
            [
                'dataSet' => 'default',
                'data' => [
                    'email' => $initialCustomer->getEmail(),
                    'password' => $customer->getPassword(),
                    'password_confirmation' => $customer->getPassword(),
                ],
            ]
        );

        $loginCustomer = $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        );
        $loginCustomer->run();

        $customerName = $initialCustomer->getFirstname() . " " . $initialCustomer->getLastname();
        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::SUCCESS_MESSAGE, $customerName),
            $customerAccountIndex->getInfoBlock()->getWelcomeText()
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer password was changed.';
    }
}
