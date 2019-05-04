<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Command\Cli\Cache;

/**
 * Check that created widget displayed on frontend in Catalog.
 */
class AssertWidgetOnFrontendInCatalog extends AbstractConstraint
{
    /**
     * Assert that created widget displayed on frontend in Catalog.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogCategoryView $catalogCategoryView
     * @param Widget $widget
     * @param Cache $cache
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogCategoryView $catalogCategoryView,
        Widget $widget,
        Cache $cache
    ) {
        // Flush cache
        $cache->flush();

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
        \PHPUnit\Framework\Assert::assertTrue(
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
