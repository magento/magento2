<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Check that created product appears in Catalog New Products List widget on frontend on Category Page.
 */
class AssertProductInCatalogNewProductsList extends AbstractConstraint
{
    /**
     * Category Page on Frontend.
     *
     * @var CatalogCategoryView
     */
    protected $catalogCategoryView;
    
    /**
     * Assert that created product appears in Catalog New Products List widget on frontend on Category Page.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductSimple $product
     * @param AdminCache $adminCache
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        CatalogProductSimple $product,
        AdminCache $adminCache,
        FixtureFactory $fixtureFactory
    ) {
        $this->catalogCategoryView = $catalogCategoryView;
        $widget = $fixtureFactory->createByCode('widget', ['dataset' => 'new_products_list_on_luma_theme']);
        $widget->persist();

        // Flush cache
        $adminCache->open();
        $adminCache->getActionsBlock()->flushMagentoCache();
        $adminCache->getMessagesBlock()->waitSuccessMessage();

        $cmsIndex->open();

        \PHPUnit_Framework_Assert::assertContains(
            $product->getName(),
            $this->catalogCategoryView->getViewBlock()->getProductsFromCatalogNewProductsListBlock(),
            'Product is absent on Catalog New Products List block on Category page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Product is present in Catalog New Products List widget on storefront Category page.";
    }
}
