<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\Mtf\Util\Command\Cli\Cache;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that created widget displayed on frontend on Home page and on Advanced Search and
 * after click on widget link on frontend system redirects you to cms page.
 */
class AssertWidgetCmsPageLink extends AbstractConstraint
{
    /**
     * Assert that created widget displayed on frontend on Home page and on Advanced Search and
     * after click on widget link on frontend system redirects you to cms page.
     *
     * @param CmsIndex $cmsIndex
     * @param Widget $widget
     * @param Cache $cache
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        Widget $widget,
        Cache $cache
    ) {
        // Flush cache
        $cache->flush();

        $cmsIndex->open();
        $widgetText = $widget->getParameters()['anchor_text'];
        \PHPUnit_Framework_Assert::assertTrue(
            $cmsIndex->getWidgetView()->isWidgetVisible($widget, $widgetText),
            'Widget with type CmsPageLink is absent on Home page.'
        );

        $title = isset($widget->getParameters()['node']) ?
            $widget->getParameters()['entities'][0]->getLabel() :
            $widget->getParameters()['entities'][0]->getContentHeading();
        $cmsIndex->getWidgetView()->clickToWidget($widget, $widgetText);
        $pageTitle = $cmsIndex->getCmsPageBlock()->getPageTitle();
        \PHPUnit_Framework_Assert::assertEquals(
            $title,
            $pageTitle,
            'Wrong page title on Cms page.'
        );

        $cmsIndex->getFooterBlock()->openAdvancedSearch();
        \PHPUnit_Framework_Assert::assertTrue(
            $cmsIndex->getWidgetView()->isWidgetVisible($widget, $widgetText),
            'Widget with type CmsPageLink is absent on Advanced Search page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Widget with type CmsPageLink is present on Home page and on Advanced Search.";
    }
}
