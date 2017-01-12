<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Store\Test\Fixture\Store;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that product is present on the category page on the custom website.
 */
class AssertProductInCategoryOnCustomWebsite extends AbstractConstraint
{
    /**
     * Assert that product is displayed on the category page.
     *
     * @param BrowserInterface $browser
     * @param CatalogCategoryView $catalogCategoryView
     * @param FixtureInterface $product
     * @param Store $store
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        CatalogCategoryView $catalogCategoryView,
        FixtureInterface $product,
        Store $store
    ) {
        // Open category view page and check if product is displayed.
        $category = $product->getDataFieldConfig('category_ids')['source']->getCategories()[0];
        $storeGroup = $store->getDataFieldConfig('group_id')['source']->getStoreGroup();
        $website = $storeGroup->getDataFieldConfig('website_id')['source']->getWebsite();
        $browser->open(
            $_ENV['app_frontend_url'] . 'websites/' . $website->getCode() . '/' . $category->getUrlKey() . '.html'
        );

        $isProductVisible = $catalogCategoryView->getListProductBlock()->getProductItem($product)->isVisible();
        while (!$isProductVisible && $catalogCategoryView->getBottomToolbar()->nextPage()) {
            $isProductVisible = $catalogCategoryView->getListProductBlock()->getProductItem($product)->isVisible();
        }

        \PHPUnit_Framework_Assert::assertTrue(
            $isProductVisible,
            'Product is absent on the category page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is present on the category page on the custom website.';
    }
}
