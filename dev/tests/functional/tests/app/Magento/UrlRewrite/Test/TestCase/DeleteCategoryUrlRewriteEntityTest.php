<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\TestCase;

use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteEdit;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for Delete Category URL Rewrites Entity
 *
 * Test Flow:
 * Preconditions:
 * 1. Create category
 * 2. Create custom category UrlRewrite
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Marketing->URL Rewrites
 * 3. Search and open created URL Rewrite
 * 4. Delete URL Rewrite
 * 5. Perform all assertions
 *
 * @group URL_Rewrites_(PS)
 * @ZephyrId MAGETWO-25086
 */
class DeleteCategoryUrlRewriteEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    /* end tags */

    /**
     * Url rewrite index page
     *
     * @var UrlRewriteIndex
     */
    protected $urlRewriteIndex;

    /**
     * Url rewrite edit page
     *
     * @var UrlRewriteEdit
     */
    protected $urlRewriteEdit;

    /**
     * Inject pages
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
     * Delete category Url Rewrite
     *
     * @param UrlRewrite $urlRewrite
     * @return void
     */
    public function test(UrlRewrite $urlRewrite)
    {
        //Precondition
        $urlRewrite->persist();
        //Steps
        $this->urlRewriteIndex->open();
        if ($urlRewrite->getRequestPath()) {
            $filter = ['request_path' => $urlRewrite->getRequestPath()];
        } else {
            $filter = ['target_path' => $urlRewrite->getTargetPath()];
        }
        $this->urlRewriteIndex->getUrlRedirectGrid()->searchAndOpen($filter);
        $this->urlRewriteEdit->getPageMainActions()->delete();
        $this->urlRewriteEdit->getModalBlock()->acceptAlert();
    }
}
