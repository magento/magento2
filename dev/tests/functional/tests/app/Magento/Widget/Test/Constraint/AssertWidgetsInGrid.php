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
 * Assert widgets are present in widget grid.
 */
class AssertWidgetsInGrid extends AbstractConstraint
{
    /**
     * Assert widgets are present in widget grid.
     * Verifying such fields as:
     * - title
     * - theme_id
     *
     * @param Widget[] $widgets
     * @param WidgetInstanceIndex $widgetInstanceIndex
     * @param AssertWidgetInGrid $assertWidgetInGrid
     * @return void
     */
    public function processAssert(
        array $widgets,
        WidgetInstanceIndex $widgetInstanceIndex,
        AssertWidgetInGrid $assertWidgetInGrid
    ) {
        foreach ($widgets as $widget) {
            $assertWidgetInGrid->processAssert($widget, $widgetInstanceIndex);
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Widgets are present in widget grid.';
    }
}
