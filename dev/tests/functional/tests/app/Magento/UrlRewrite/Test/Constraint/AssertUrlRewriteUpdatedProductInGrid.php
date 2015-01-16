<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertUrlRewriteUpdatedProductInGrid
 * Assert that product url in url rewrite grid..
 */
class AssertUrlRewriteUpdatedProductInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that product url in url rewrite grid.
     *
     * @param CatalogProductSimple $product
     * @param CatalogProductSimple $initialProduct
     * @param UrlRewriteIndex $urlRewriteIndex
     * @return void
     */
    public function processAssert(
        CatalogProductSimple $product,
        CatalogProductSimple $initialProduct,
        UrlRewriteIndex $urlRewriteIndex
    ) {
        $urlRewriteIndex->open();
        $category = $product->getDataFieldConfig('category_ids')['source']->getCategories()[0];
        $targetPath = "catalog/product/view/id/{$initialProduct->getId()}/category/{$category->getId()}";
        $url = strtolower($product->getCategoryIds()[0] . '/' . $product->getUrlKey());
        $filter = [
            'request_path' => $url,
            'target_path' => $targetPath,
        ];
        \PHPUnit_Framework_Assert::assertTrue(
            $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible($filter, true, false),
            "URL Rewrite with request path '$url' is absent in grid."
        );

        $categoryInitial = $initialProduct->getDataFieldConfig('category_ids')['source']->getCategories()[0];
        $targetPath = "catalog/product/view/id/{$initialProduct->getId()}/category/{$categoryInitial->getId()}";

        \PHPUnit_Framework_Assert::assertFalse(
            $urlRewriteIndex->getUrlRedirectGrid()->isRowVisible(['target_path' => $targetPath], true, false),
            "URL Rewrite with target path '$targetPath' is present in grid."
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'URL Rewrite for product was changed after assign category.';
    }
}
