<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceIndex;

/**
 * Assert theme filter contains all possible values from created widgets.
 */
class AssertThemeFilterValuesOnWidgetGrid extends AbstractConstraint
{
    /**
     * Assert theme filter contains all possible values from created widgets.
     *
     * @param Widget[] $widgets
     * @param WidgetInstanceIndex $widgetInstanceIndex
     * @return void
     */
    public function processAssert(array $widgets, WidgetInstanceIndex $widgetInstanceIndex)
    {
        $expectedValues = [];
        foreach ($widgets as $widget) {
            $expectedValues[] = $widget->getThemeId();
        }
        $widgetInstanceIndex->open();
        $actualValues = $widgetInstanceIndex->getWidgetGrid()->getThemeIdValues();
        \PHPUnit_Framework_Assert::assertEmpty(
            array_diff($expectedValues, $actualValues),
            'Widget grid theme filter doesn\'t contain all possible values from created widgets.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Widget grid theme filter contains all possible values from created widgets.';
    }
}
