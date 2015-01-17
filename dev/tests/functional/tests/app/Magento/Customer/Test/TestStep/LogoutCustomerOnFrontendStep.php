<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestStep;

use Magento\Cms\Test\Page\CmsIndex;
use Mtf\TestStep\TestStepInterface;

/**
 * Class LogoutCustomerOnFrontendStep
 * Logout customer on frontend
 */
class LogoutCustomerOnFrontendStep implements TestStepInterface
{
    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * @constructor
     * @param CmsIndex $cmsIndex
     */
    public function __construct(CmsIndex $cmsIndex)
    {
        $this->cmsIndex = $cmsIndex;
    }

    /**
     * Logout customer
     *
     * @return void
     */
    public function run()
    {
        $this->cmsIndex->open();
        if ($this->cmsIndex->getLinksBlock()->isLinkVisible("Log Out")) {
            $this->cmsIndex->getLinksBlock()->openLink("Log Out");
            $this->cmsIndex->getCmsPageBlock()->waitUntilTextIsVisible('Home Page');
        }
    }
}
