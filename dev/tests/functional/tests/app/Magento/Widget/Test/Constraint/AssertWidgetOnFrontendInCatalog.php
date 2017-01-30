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

/**
 * Check that created widget displayed on frontent in Catalog.
 */
class AssertWidgetOnFrontendInCatalog extends AbstractConstraint
{
    /**
     * Assert that created widget displayed on frontent in Catalog.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param Widget $widget
     * @param AdminCache $adminCache
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        Widget $widget,
        AdminCache $adminCache
    ) {
        // Flush cache
        $adminCache->open();
        $adminCache->getActionsBlock()->flushMagentoCache();
        $adminCache->getMessagesBlock()->waitSuccessMessage();

        $cmsIndex->open();
        if (isset($widget->getWidgetInstance()[0]['entities'])) {
            $categoryName = $widget->getWidgetInstance()[0]['entities']->getName();
        } else {
            $categoryName = $widget->getParameters()['entities']->getCategoyId()[0];
        }
        if ($widget->getCode() == 'CMS Static Block') {
            $widgetText = $widget->getParameters()['entities'][0]->getContent();
        } else {
            $widgetText = $widget->getParameters()['anchor_text'];
        }
        $cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
        \PHPUnit_Framework_Assert::assertTrue(
            $catalogCategoryView->getWidgetView()->isWidgetVisible($widget, $widgetText),
            'Widget is absent on Category page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Widget is present on Category page.";
    }
}
