<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Constraint;

use Magento\Mtf\Util\Command\Cli\Cache;
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
     * @param Cache $cache
     * @return void
     */
    public function processAssert(
        CatalogProductView $productView,
        BrowserInterface $browser,
        Widget $widget,
        Cache $cache
    ) {
        // Flush cache
        $cache->flush();

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
