<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\TestCase;

use Magento\Catalog\Test\Fixture\Category;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteEdit;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Precondition:
 * 1. SubCategory is created.
 * 2. Category URL Rewrite is created.
 *
 * Steps:
 * 1. Log in to backend as Admin.
 * 2. Go to the Marketing > SEO & Search > URL Rewrites.
 * 3. Click Category URL Rewrite from grid.
 * 4. Edit test value(s) according to dataset.
 * 5. Click 'Save' button.
 * 6. Perform all asserts.
 *
 * @group URL_Rewrites
 * @ZephyrId MAGETWO-24838
 */
class UpdateCategoryUrlRewriteEntityTest extends Injectable
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
     * Prepare datasets and pages.
     *
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param UrlRewriteEdit $urlRewriteEdit
     * @param FixtureFactory $fixtureFactory
     * @param Category $category
     * @return array
     */
    public function __inject(
        UrlRewriteIndex $urlRewriteIndex,
        UrlRewriteEdit $urlRewriteEdit,
        FixtureFactory $fixtureFactory,
        Category $category
    ) {
        $this->urlRewriteIndex = $urlRewriteIndex;
        $this->urlRewriteEdit = $urlRewriteEdit;
        $category->persist();
        $categoryRedirect = $fixtureFactory->createByCode(
            'urlRewrite',
            [
                'dataset' => 'default',
                'data' => ['target_path' => $category->getUrlKey() . '.html']
            ]
        );
        $categoryRedirect->persist();

        return ['categoryRedirect' => $categoryRedirect, 'category' => $category];
    }

    /**
     * Update category URL rewrites.
     *
     * @param UrlRewrite $categoryRedirect
     * @param UrlRewrite $urlRewrite
     * @return void
     */
    public function test(UrlRewrite $categoryRedirect, UrlRewrite $urlRewrite)
    {
        //Steps
        $this->urlRewriteIndex->open();
        $filter = ['request_path' => $categoryRedirect->getRequestPath()];
        $this->urlRewriteIndex->getUrlRedirectGrid()->searchAndOpen($filter);
        $this->urlRewriteEdit->getFormBlock()->fill($urlRewrite);
        $this->urlRewriteEdit->getPageMainActions()->save();
    }
}
