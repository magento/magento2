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
    const LOGOUT_PAGE_TITLE = 'You are signed out.';

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
        $this->cmsIndex->open();
        $this->cmsIndex->getCmsPageBlock()->waitPageInit();
        if ($this->cmsIndex->getLinksBlock()->isLinkVisible('Sign Out')) {
            $this->cmsIndex->getLinksBlock()->openLink('Sign Out');
            $this->cmsIndex->getCmsPageBlock()->waitUntilTextIsVisible('Home Page');
            $this->cmsIndex->getCmsPageBlock()->waitPageInit();
        }
    }
}
