<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestStep;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Customer\Test\Page\CustomerAccountIndex;

/**
 * Logout customer on frontend.
 */
class LogoutCustomerOnFrontendStep implements TestStepInterface
{
    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Customer account page.
     *
     * @var CustomerAccountIndex
     */
    protected $customerAccount;

    /**
     * @constructor
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountIndex $customerAccount
     */
    public function __construct(CmsIndex $cmsIndex, CustomerAccountIndex $customerAccount)
    {
        $this->cmsIndex = $cmsIndex;
        $this->customerAccount = $customerAccount;
    }

    /**
     * Logout customer.
     *
     * @return void
     */
    public function run()
    {
        $this->customerAccount->open();
        $this->cmsIndex->getCmsPageBlock()->waitPageInit();
        if ($this->cmsIndex->getTitleBlock()->getTitle() === 'My Dashboard') {
            $this->cmsIndex->getLinksBlock()->openLink('Sign Out');
            $this->cmsIndex->getCmsPageBlock()->waitUntilTextIsVisible('Home Page');
            $this->cmsIndex->getCmsPageBlock()->waitPageInit();
        }
    }
}
