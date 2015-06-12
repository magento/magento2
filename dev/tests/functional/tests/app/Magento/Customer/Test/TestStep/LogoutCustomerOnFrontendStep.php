<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestStep;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Logout customer on frontend.
 */
class LogoutCustomerOnFrontendStep implements TestStepInterface
{
    /**
     * Logout page title.
     */
    const LOGOUT_PAGE_TITLE = 'You are now logged out';

    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Customer logout page.
     *
     * @var CustomerAccountLogout
     */
    protected $customerAccountLogout;

    /**
     * @constructor
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountLogout $customerAccountLogout
     */
    public function __construct(CmsIndex $cmsIndex, CustomerAccountLogout $customerAccountLogout)
    {
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountLogout = $customerAccountLogout;
    }

    /**
     * Logout customer.
     *
     * @return void
     */
    public function run()
    {
        /* @TODO: MAGETWO-37391
         * $this->cmsIndex->open();
         * $this->cmsIndex->getCmsPageBlock()->waitPageInit();
         * if ($this->cmsIndex->getLinksBlock()->isLinkVisible("Log Out")) {
         * $this->cmsIndex->getLinksBlock()->openLink("Log Out");
         * $this->cmsIndex->getCmsPageBlock()->waitUntilTextIsVisible('Home Page');
         * $this->cmsIndex->getCmsPageBlock()->waitPageInit();
         * }
         */
        $this->customerAccountLogout->open();
        if (self::LOGOUT_PAGE_TITLE == $this->cmsIndex->getCmsPageBlock()->getPageTitle()) {
            $this->cmsIndex->getCmsPageBlock()->waitUntilTextIsVisible('Home Page');
        }
        $this->cmsIndex->getCmsPageBlock()->waitPageInit();
    }
}
