<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Command\Cli\Cache;

/**
 * Check that created widget does NOT displayed on frontend on Home page.
 */
class AssertWidgetAbsentOnFrontendHome extends AbstractConstraint
{
    /**
     * Assert that created widget is absent on frontend on Home page.
     *
     * @param CmsIndex $cmsIndex
     * @param Widget $widget
     * @param Cache $cache
     * @param array $caches [optional]
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        Widget $widget,
        Cache $cache,
        array $caches = []
    ) {
        // Flush cache
        if (!in_array('Invalidated', $caches)) {
            $cache->flush();
        }
        $cmsIndex->open();
        $widgetText = $widget->getParameters()['anchor_text'];
        \PHPUnit\Framework\Assert::assertFalse(
            $cmsIndex->getWidgetView()->isWidgetVisible($widget, $widgetText),
            'Widget is present on Home page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     * @return string
     */
    public function toString()
    {
        return "Widget is absent on Home page.";
    }
}
