<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
 * Test Creation for Create Category Rewrites Entity
 *
 * Precondition:
 * 1. Create Sub-category
 *
 * Test Flow:
 * 1. Login to backend as Admin
 * 2. Go to the Marketing-> SEO & Search->URL Rewrites
 * 3. Click "+" button
 * 4. Select "For Category" in Create URL Rewrite dropdown
 * 5. Select Category in "Category tree"
 * 6. Fill data according to data set
 * 7. Save Rewrite
 * 8. Verify created rewrite
 *
 * @group URL_Rewrites_(PS)
 * @ZephyrId MAGETWO-24280
 */
class CreateCategoryRewriteEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Page of url rewrite edit category
     *
     * @var UrlRewriteEdit
     */
    protected $urlRewriteEdit;

    /**
     * Main page of url rewrite
     *
     * @var UrlRewriteIndex
     */
    protected $urlRewriteIndex;

    /**
     * Inject page
     *
     * @param UrlRewriteEdit $urlRewriteEdit
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param FixtureFactory $fixtureFactory
     * @return array
     */
    public function __inject(
        UrlRewriteEdit $urlRewriteEdit,
        UrlRewriteIndex $urlRewriteIndex,
        FixtureFactory $fixtureFactory
    ) {
        $this->urlRewriteEdit = $urlRewriteEdit;
        $this->urlRewriteIndex = $urlRewriteIndex;
        $category = $fixtureFactory->createByCode(
            'category',
            ['dataset' => 'default_subcategory']
        );
        $category->persist();
        return ['category' => $category];
    }

    /**
     * Test check create category rewrites
     *
     * @param UrlRewrite $urlRewrite
     * @param Category $category
     * @return void
     */
    public function test(UrlRewrite $urlRewrite, Category $category)
    {
        //Steps
        $this->urlRewriteIndex->open();
        $this->urlRewriteIndex->getPageActionsBlock()->addNew();
        $this->urlRewriteEdit->getFormBlock()->fill($urlRewrite);
        $this->urlRewriteEdit->getTreeBlock()->selectCategory($category);
        $this->urlRewriteEdit->getFormBlock()->fill($urlRewrite);
        $this->urlRewriteEdit->getPageMainActions()->save();
    }
}
