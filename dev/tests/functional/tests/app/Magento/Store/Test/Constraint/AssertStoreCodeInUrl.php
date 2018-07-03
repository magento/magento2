<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Assert store code in the home page url.
     *
     * @param CmsIndex $cmsIndex
     * @param BrowserInterface $browser
     * @param string $storeCode
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, BrowserInterface $browser, $storeCode)
    {
        $cmsIndex->open();
        $cmsIndex->getLogoBlock()->clickOnLogo();
        \PHPUnit\Framework\Assert::assertEquals(
            $_ENV['app_frontend_url'] . $storeCode . '/',
            $browser->getUrl(),
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
