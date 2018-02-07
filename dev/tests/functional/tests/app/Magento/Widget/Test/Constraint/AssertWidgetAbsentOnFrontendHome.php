<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that created widget does NOT displayed on frontend on Home page
 */
class AssertWidgetAbsentOnFrontendHome extends AbstractConstraint
{
    /**
     * Assert that created widget is absent on frontend on Home page
     *
     * @param CmsIndex $cmsIndex
     * @param Widget $widget
     * @param AdminCache $adminCache
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        Widget $widget,
        AdminCache $adminCache
    ) {
        // Flush cache
        $adminCache->open();
        $adminCache->getActionsBlock()->flushMagentoCache();
        $adminCache->getMessagesBlock()->waitSuccessMessage();

        $cmsIndex->open();
        $widgetText = $widget->getParameters()['anchor_text'];
        \PHPUnit_Framework_Assert::assertFalse(
            $cmsIndex->getWidgetView()->isWidgetVisible($widget, $widgetText),
            'Widget is present on Home page.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     * @return string
     */
    public function toString()
    {
        return "Widget is absent on Home page.";
    }
}
