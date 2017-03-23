<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\Variable\Test\Page\Adminhtml\SystemVariableNew;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that created CMS Page URL Rewrite lead to appropriate page in frontend.
 */
class AssertUrlRewriteCmsPageRedirect extends AbstractConstraint
{
    /**
     * Assert that created CMS Page URL Rewrite lead to appropriate page in frontend.
     *
     * @param UrlRewrite $urlRewrite
     * @param CmsPage $cmsPage
     * @param SystemVariableNew $systemVariableNew
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(
        UrlRewrite $urlRewrite,
        CmsPage $cmsPage,
        SystemVariableNew $systemVariableNew,
        BrowserInterface $browser
    ) {
        $browser->open($_ENV['app_frontend_url'] . $urlRewrite->getRequestPath());
        if ($urlRewrite->hasData('store_id')) {
            $store = explode('/', $urlRewrite->getStoreId());
            $systemVariableNew->getFormPageActions()->selectStoreView($store[2]);
        }
        $url = $urlRewrite->getRedirectType() == 'No'
            ? $urlRewrite->getRequestPath()
            : $cmsPage->getTitle();

        \PHPUnit_Framework_Assert::assertEquals(
            $_ENV['app_frontend_url'] . $url,
            $browser->getUrl(),
            'URL rewrite CMS Page redirect false.'
        );
    }

    /**
     * URL Rewrite lead to appropriate page in frontend.
     *
     * @return string
     */
    public function toString()
    {
        return 'URL Rewrite lead to appropriate page in frontend.';
    }
}
