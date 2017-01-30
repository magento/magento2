<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\TestCase;

use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteEdit;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Test Creation for Update Product URL Rewrites Entity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create custom store view
 * 2. Create simple product
 * 3. Create product UrlRewrite
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Marketing->Url Redirects
 * 3. Search and open created Url Redirect
 * 4. Fill data according to dataset
 * 5. Perform all assertions
 *
 * @group URL_Rewrites_(PS)
 * @ZephyrId MAGETWO-24819
 */
class UpdateProductUrlRewriteEntityTest extends Injectable
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
     * Prepare datasets and pages
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param UrlRewriteEdit $urlRewriteEdit
     * @return array
     */
    public function __inject(
        UrlRewriteIndex $urlRewriteIndex,
        UrlRewriteEdit $urlRewriteEdit
    ) {
        $this->urlRewriteIndex = $urlRewriteIndex;
        $this->urlRewriteEdit = $urlRewriteEdit;
    }

    /**
     * Update product URL rewrites
     *
     * @param UrlRewrite $urlRewrite
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function test(UrlRewrite $urlRewrite, FixtureFactory $fixtureFactory)
    {
        /** @var UrlRewrite $productRedirect */
        $productRedirect = $fixtureFactory->createByCode(
            'urlRewrite',
            [
                'dataset' => 'default',
                'data' => ['target_path' => $urlRewrite->getTargetPath()]
            ]
        );
        $productRedirect->persist();
        //Steps
        $this->urlRewriteIndex->open();
        $filter = ['request_path' => $productRedirect->getRequestPath()];
        $this->urlRewriteIndex->getUrlRedirectGrid()->searchAndOpen($filter);
        $this->urlRewriteEdit->getFormBlock()->fill($urlRewrite);
        $this->urlRewriteEdit->getPageMainActions()->save();
    }
}
