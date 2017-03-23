<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that customer success log out.
 */
class AssertCustomerLogout extends AbstractConstraint
{
    /**
     * Logout page title.
     */
    const LOGOUT_PAGE_TITLE = 'You are signed out';

    /**
     * Home page title.
     */
    const HOME_PAGE_TITLE = 'Home Page';

    /**
     * Assert that customer success log out.
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function processAssert(CustomerAccountIndex $customerAccountIndex, CmsIndex $cmsIndex)
    {
        $customerAccountIndex->open();
        $cmsIndex->getCmsPageBlock()->waitPageInit();

        $cmsIndex->getLinksBlock()->openLink('Sign Out');
        $cmsIndex->getCmsPageBlock()->waitUntilTextIsVisible(self::LOGOUT_PAGE_TITLE);
        $cmsIndex->getCmsPageBlock()->waitUntilTextIsVisible(self::HOME_PAGE_TITLE);
        $cmsIndex->getCmsPageBlock()->waitPageInit();
        \PHPUnit_Framework_Assert::assertTrue(
            $cmsIndex->getLinksBlock()->isLinkVisible('Sign In'),
            "Customer wasn't logged out."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Customer is successfully log out.";
    }
}
