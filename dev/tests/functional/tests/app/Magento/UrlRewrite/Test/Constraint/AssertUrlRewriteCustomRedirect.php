<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteCustomRedirect
 * Assert check URL rewrite custom redirect
 */
class AssertUrlRewriteCustomRedirect extends AbstractConstraint
{
    /**
     * Assert check URL rewrite custom redirect
     *
     * @param UrlRewrite $urlRewrite
     * @param BrowserInterface $browser
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function processAssert(UrlRewrite $urlRewrite, BrowserInterface $browser, CmsIndex $cmsIndex)
    {
        $browser->open($_ENV['app_frontend_url'] . $urlRewrite->getRequestPath());
        $entity = $urlRewrite->getDataFieldConfig('target_path')['source']->getEntity();
        $title = $entity->hasData('name') ? $entity->getName() : $entity->getContentHeading();
        $pageTitle = $cmsIndex->getTitleBlock()->getTitle();
        \PHPUnit_Framework_Assert::assertEquals(
            $pageTitle,
            $title,
            'URL rewrite product redirect false.'
            . "\nExpected: " . $title
            . "\nActual: " . $pageTitle
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Custom URL rewrite redirect was success.';
    }
}
