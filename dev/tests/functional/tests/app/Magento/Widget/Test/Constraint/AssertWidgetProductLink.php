<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\Mtf\Util\Command\Cli\Cache;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that after click on widget link on frontend system redirects you to Product page defined in widget.
 */
class AssertWidgetProductLink extends AbstractConstraint
{
    /**
     * Assert that after click on widget link on frontend system redirects you to Product page defined in widget.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogProductView $productView
     * @param Widget $widget
     * @param Cache $cache
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogProductView $productView,
        Widget $widget,
        Cache $cache
    ) {
        // Flush cache
        $cache->flush();

        $cmsIndex->open();
        $cmsIndex->getTopmenu()->selectCategoryByName($widget->getWidgetInstance()[0]['entities']->getName());
        $cmsIndex->getWidgetView()->clickToWidget($widget, $widget->getParameters()['anchor_text']);
        $title = $productView->getTitleBlock()->getTitle();
        \PHPUnit_Framework_Assert::assertEquals(
            $widget->getParameters()['entities'][0]->getName(),
            $title,
            'Wrong product title.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Widget link on frontend system redirects to Product page defined in widget.";
    }
}
