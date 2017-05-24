<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\Widget\Test\Fixture\Widget;
use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert widget is present in widget grid.
 */
class AssertWidgetInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'high';
    /* end tags */

    /**
     * Assert widget availability in widget grid.
     * Verifying such fields as:
     * - title
     * - theme_id
     *
     * @param Widget $widget
     * @param WidgetInstanceIndex $widgetInstanceIndex
     * @return void
     */
    public function processAssert(Widget $widget, WidgetInstanceIndex $widgetInstanceIndex)
    {
        $filter = ['title' => $widget->getTitle(), 'theme_id' => $widget->getThemeId()];
        $widgetInstanceIndex->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $widgetInstanceIndex->getWidgetGrid()->isRowVisible($filter),
            'Widget with title \'' . $widget->getTitle() . '\' is absent in Widget grid.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Widget is present in widget grid.';
    }
}
