<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\Widget\Test\Fixture\Widget;
use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertWidgetInGrid
 */
class AssertWidgetWithDifferentThemesArePresentedInGrid extends AbstractConstraint
{
    /**
     * Assert widgets with different themes availability in widget grid
     *
     * @param Widget $widget
     * @param WidgetInstanceIndex $widgetInstanceIndex
     * @param array $additionalWidgets
     * @return void
     */
    public function processAssert(
        Widget $widget,
        WidgetInstanceIndex $widgetInstanceIndex,
        array $additionalWidgets = []
    ) {
        $additionalWidgets[] = $widget;
        $expectedData = [];
        $actualData = [];
        $widgetInstanceIndex->open();
        $widgetGrid = $widgetInstanceIndex->getWidgetGrid();

        foreach ($additionalWidgets as $widget) {
            $filter = [
                'title' => $widget->getTitle(),
                'theme_id' => $widget->getThemeId(),
            ];
            $widgetGrid->search($filter);
            $expectedData[] = $filter;
            $actualData[] = $widgetGrid->getRowsData(array_keys($filter))[0];
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $expectedData,
            $actualData,
            'Not all expected widgets are present in grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Widgets with different themes are present in widget grid.';
    }
}
