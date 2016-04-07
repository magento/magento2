<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\Backend\Test\Page\Adminhtml\AdminCache;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that created widget displayed on frontend on Product page.
 */
class AssertWidgetOnProductPage extends AbstractConstraint
{
    /**
     * Assert that created widget displayed on frontend on Product page.
     *
     * @param CatalogProductView $productView
     * @param BrowserInterface $browser
     * @param Widget $widget
     * @param AdminCache $adminCache
     * @return void
     */
    public function processAssert(
        CatalogProductView $productView,
        BrowserInterface $browser,
        Widget $widget,
        AdminCache $adminCache
    ) {
        // Flush cache
        $adminCache->open();
        $adminCache->getActionsBlock()->flushMagentoCache();
        $adminCache->getMessagesBlock()->waitSuccessMessage();

        $urlKey = $widget->getWidgetInstance()[0]['entities']['url_key'];
        $browser->open($_ENV['app_frontend_url'] . $urlKey . '.html');
        $widgetText = $widget->getParameters()['link_text'];

        \PHPUnit_Framework_Assert::assertTrue(
            $productView->getWidgetView()->isWidgetVisible($widget, $widgetText),
            'Widget is absent on Product page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Widget is present on Product page.";
    }
}
