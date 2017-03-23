<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Check that created Catalog New Products List widget displayed on frontend on Category Page.
 */
class AssertWidgetCatalogNewProductsList extends AbstractConstraint
{
    /**
     * Category Page on Frontend.
     *
     * @var CatalogCategoryView
     */
    protected $catalogCategoryView;
    
    /**
     * Assert that created Catalog New Products List widget displayed on frontend on Category Page.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param CatalogProductSimple $productSimple1
     * @param CatalogProductSimple $productSimple2
     * @param Widget $widget
     * @param AdminCache $adminCache
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        CatalogProductSimple $productSimple1,
        CatalogProductSimple $productSimple2,
        Widget $widget,
        AdminCache $adminCache
    ) {
        $this->catalogCategoryView = $catalogCategoryView;
        
        // Flush cache
        $adminCache->open();
        $adminCache->getActionsBlock()->flushMagentoCache();
        $adminCache->getMessagesBlock()->waitSuccessMessage();

        $productSimple1->persist();
        $productSimple2->persist();
        $products[] = $productSimple2->getName();
        $products[] = $productSimple1->getName();

        $cmsIndex->open();
        $categoryName = $widget->getWidgetInstance()[0]['entities']->getName();
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
        \PHPUnit_Framework_Assert::assertTrue(
            $catalogCategoryView->getWidgetView()->isWidgetVisible($widget, 'New Products'),
            'Widget is absent on Category page.'
        );
        \PHPUnit_Framework_Assert::assertEquals(
            $products,
            $this->catalogCategoryView->getViewBlock()->getProductsFromCatalogNewProductsListBlock(),
            'There are wrong products or products are absent on Catalog New Products List block on Category page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Catalog New Products List widget is present on Category page.";
    }
}
