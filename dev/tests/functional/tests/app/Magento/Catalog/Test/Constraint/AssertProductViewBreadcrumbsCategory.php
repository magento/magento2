<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that correct category is displayed in breadcrumbs.
 */
class AssertProductViewBreadcrumbsCategory extends AbstractConstraint
{
    /**
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param CatalogProductSimple $product
     * @return void
     */
    public function processAssert(
        CatalogCategoryView $catalogCategoryView,
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        CatalogProductSimple $product
    ) {
        $categories = is_object($product->getDataFieldConfig('category_ids')['source']) ?
            $product->getDataFieldConfig('category_ids')['source']->getCategories()
            : [];

        if (!empty($categories)) {

            /** @var Category $category */
            foreach ($categories as $category) {
                $browser->open($_ENV['app_frontend_url'] . $category->getUrlKey() . '.html');

                $productItem = $catalogCategoryView->getListProductBlock()->getProductItem($product);
                \PHPUnit_Framework_Assert::assertTrue(
                    $productItem->isVisible(),
                    'Product is not present in category.'
                );

                $productItem->open();
                // Ensure page is cached
                $browser->refresh();

                $breadcrumbs = $catalogProductView->getBreadcrumbs()->getCrumbs();

                \PHPUnit_Framework_Assert::assertContains(
                    $category->getName(),
                    $breadcrumbs,
                    'Product view page has incorrect breadcrumbs.'
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return 'Product has correct category in product view breadcrumbs.';
    }
}
