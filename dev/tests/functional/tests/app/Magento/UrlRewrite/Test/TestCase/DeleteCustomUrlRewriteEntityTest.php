<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\TestCase;

use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteEdit;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Custom URL Rewrites is created.
 *
 * Steps:
 * 1. Login to backend as Admin.
 * 2. Go to the Marketing > SEO & Search > URL Redirects.
 * 3. Search and open created URL Redirect.
 * 4. Delete Redirect.
 * 5. Perform all assertions.
 *
 * @group URL_Rewrites
 * @ZephyrId MAGETWO-26337
 */
class DeleteCustomUrlRewriteEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Url rewrite index page.
     *
     * @var UrlRewriteIndex
     */
    protected $urlRewriteIndex;

    /**
     * Url rewrite edit page.
     *
     * @var UrlRewriteEdit
     */
    protected $urlRewriteEdit;

    /**
     * Inject pages.
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param UrlRewriteEdit $urlRewriteEdit
     * @return void
     */
    public function __inject(UrlRewriteIndex $urlRewriteIndex, UrlRewriteEdit $urlRewriteEdit)
    {
        $this->urlRewriteIndex = $urlRewriteIndex;
        $this->urlRewriteEdit = $urlRewriteEdit;
    }

    /**
     * Delete custom URL Rewrite.
     *
     * @param UrlRewrite $urlRewrite
     * @return void
     */
    public function test(UrlRewrite $urlRewrite)
    {
        // Precondition
        $urlRewrite->persist();

        // Steps
        $this->urlRewriteIndex->open();
        $filter = ['request_path' => $urlRewrite->getRequestPath()];
        $this->urlRewriteIndex->getUrlRedirectGrid()->searchAndOpen($filter);
        $this->urlRewriteEdit->getPageMainActions()->delete();
        $this->urlRewriteEdit->getModalBlock()->acceptAlert();
    }
}
