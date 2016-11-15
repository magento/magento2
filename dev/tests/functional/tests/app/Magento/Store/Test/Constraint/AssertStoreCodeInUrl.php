<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert that store code is present in the url.
 */
class AssertStoreCodeInUrl extends AbstractConstraint
{
    /**
     * Assert store code in the url of logo image.
     *
     * @param CmsIndex $cmsIndex
     * @param BrowserInterface $browser
     * @param $storeCode
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, BrowserInterface $browser, $storeCode)
    {
        $cmsIndex->open();
        $cmsIndex->getLogoBlock()->clickOnLogo();
        \PHPUnit_Framework_Assert::assertTrue(
            strpos($browser->getUrl(), '/' . $storeCode) !== false,
            sprintf('Store code \'%s\' is not present in the url: %s', $storeCode, $browser->getUrl())
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Store code is present in the url.';
    }
}
