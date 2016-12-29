<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\UrlRewrite\Test\Fixture\UrlRewrite;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogCategoryIndex;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;

/**
 * Assert that url rewrites for product and category are deleted after deleting category.
 */
class AssertUrlRewriteAfterDeletingCategory extends AbstractConstraint
{
    /**
     * Assert that url rewrites are not present in grid.
     *
     * @param UrlRewrite $urlRewrite
     * @param CatalogProductSimple $product
     * @param CatalogCategoryIndex $catalogCategoryIndex
     * @param CatalogCategoryEdit $catalogCategoryEdit
     * @param UrlRewriteIndex $urlRewriteIndex
     * @param AssertUrlRewriteNotInGrid $assertUrlRewrite
     * @param AssertUrlRewriteCategoryNotInGrid $assertCategoryUrlRewrite
     * @return void
     */
    public function processAssert(
        UrlRewrite $urlRewrite,
        CatalogProductSimple $product,
        CatalogCategoryIndex $catalogCategoryIndex,
        CatalogCategoryEdit $catalogCategoryEdit,
        UrlRewriteIndex $urlRewriteIndex,
        AssertUrlRewriteNotInGrid $assertUrlRewrite,
        AssertUrlRewriteCategoryNotInGrid $assertCategoryUrlRewrite
    ) {
        $category = $product->getDataFieldConfig('category_ids')['source']->getCategories()[0];
        $catalogCategoryIndex->open();
        $catalogCategoryIndex->getTreeCategories()->selectCategory($category);
        $catalogCategoryEdit->getFormPageActions()->delete();
        $catalogCategoryEdit->getModalBlock()->acceptAlert();

        $assertCategoryUrlRewrite->processAssert($urlRewriteIndex, $category);
        $assertUrlRewrite->processAssert($urlRewriteIndex, $urlRewrite);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'URL rewrites are deleted.';
    }
}
