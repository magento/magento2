<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block;

use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Widget block on the frontend.
 */
class WidgetView extends Block
{
    /**
     * Widgets selectors.
     *
     * @var array
     */
    protected $widgetSelectors = [
        'cmsPageLink' => '/descendant-or-self::div//a[contains(.,"%s")]',
        'catalogCategoryLink' => '//a[contains(.,"%s")]',
        'catalogProductLink' => '//a[contains(.,"%s")]',
        'recentlyComparedProducts' => '/descendant-or-self::div[contains(.,"%s")]',
        'recentlyViewedProducts' => '/descendant-or-self::div[contains(.,"%s")]',
        'cmsStaticBlock' => '/descendant-or-self::div[contains(.,"%s")]',
    ];

    /**
     * Check is visible widget selector.
     *
     * @param Widget $widget
     * @param string $widgetText
     * @return bool
     * @throws \Exception
     */
    public function isWidgetVisible(Widget $widget, $widgetText)
    {
        $widgetType = $this->getWidgetType($widget);
        if ($this->hasRender($widgetType)) {
            return $this->callRender(
                $widgetType,
                'isWidgetVisible',
                ['widget' => $widget, 'widgetText' => $widgetText]
            );
        } else {
            if (isset($this->widgetSelectors[$widgetType])) {
                return $this->_rootElement->find(
                    sprintf($this->widgetSelectors[$widgetType], $widgetText),
                    Locator::SELECTOR_XPATH
                )->isVisible();
            } else {
                throw new \Exception('Determine how to find the widget on the page.');
            }
        }
    }

    /**
     * Click to widget selector.
     *
     * @param Widget $widget
     * @param string $widgetText
     * @return void
     * @throws \Exception
     */
    public function clickToWidget(Widget $widget, $widgetText)
    {
        $widgetType = $this->getWidgetType($widget);
        if ($this->hasRender($widgetType)) {
            $this->callRender($widgetType, 'clickToWidget', ['widget' => $widget, 'widgetText' => $widgetText]);
        } else {
            if (isset($this->widgetSelectors[$widgetType])) {
                $this->_rootElement->find(
                    sprintf($this->widgetSelectors[$widgetType], $widgetText),
                    Locator::SELECTOR_XPATH
                )->click();
            } else {
                throw new \Exception('Determine how to find the widget on the page.');
            }
        }
    }

    /**
     * Get widget type based on widget code.
     *
     * @param Widget $widget
     * @return string
     */
    protected function getWidgetType(Widget $widget)
    {
        return lcfirst(str_replace(' ', '', ucwords(strtolower($widget->getCode()))));
    }
}
