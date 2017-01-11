<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteEdit;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create custom storeView.
 * 2. Create simple product.
 *
 * Steps:
 * 1. Open Backend.
 * 2. Go to Marketing > Url Redirects.
 * 3. Click "Add URL Rewrite" button.
 * 4. Select "For Product" from "Create URL Rewrite:" dropdown.
 * 5. Select created early product.
 * 6. Click "Skip Category Selection" button.
 * 7. Fill data according to dataset.
 * 8. Perform all assertions.
 *
 * @group URL_Rewrites
 * @ZephyrId MAGETWO-25150
 */
class CreateProductUrlRewriteEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
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
     * Prepare pages.
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
     * Create product URL Rewrite.
     *
     * @param CatalogProductSimple $product
     * @param UrlRewrite $urlRewrite
     * @return void
     */
    public function test(CatalogProductSimple $product, UrlRewrite $urlRewrite)
    {
        //Precondition
        $product->persist();
        $filter = ['id' => $product->getId()];
        //Steps
        $this->urlRewriteIndex->open();
        $this->urlRewriteIndex->getPageActionsBlock()->addNew();
        $this->urlRewriteEdit->getFormBlock()->fill($urlRewrite);
        $this->urlRewriteEdit->getProductGridBlock()->searchAndOpen($filter);
        $category = $product->hasData('category_ids')
            ? $product->getDataFieldConfig('category_ids')['source']->getCategories()[0]
            : null;
        $this->urlRewriteEdit->getTreeBlock()->selectCategory($category);
        $this->urlRewriteEdit->getFormBlock()->fill($urlRewrite);
        $this->urlRewriteEdit->getPageMainActions()->save();
    }
}
