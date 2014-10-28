<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\UrlRewrite\Test\Constraint;

use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\UrlRewrite\Test\Page\Adminhtml\UrlRewriteIndex;

/**
 * Class AssertUrlRewriteUpdatedProductInGrid
 * Assert that product url in url rewrite grid..
 */
class AssertUrlRewriteUpdatedProductInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'low';

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
